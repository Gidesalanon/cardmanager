<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Eleve;
use App\Models\SchoolYear;
use App\Models\Serie;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AdminStudentImportController extends Controller
{
    public function create()
    {
        return view('admin.eleves.import.create', [
            'activeYear' => SchoolYear::active()->firstOrFail(),
            'classes'    => Classe::select('id', 'nom')->orderBy('nom')->get()->unique('nom')->values(),
            'ecoles'     => Ecole::orderBy('nom_ecole')->get(),
        ]);
    }

    // Nettoie _x000d_ et \r injectés par PhpSpreadsheet depuis Excel Windows
    private function cleanVal(string $value): string
    {
        $value = preg_replace('/_x000[Dd]_/u', ' ', $value);
        $value = preg_replace('/\r\n|\r|\n/', ' ', $value);
        return trim(preg_replace('/\s{2,}/', ' ', $value));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('document')->getPathname());
            $worksheet   = $spreadsheet->getActiveSheet();

            $highestRow          = $worksheet->getHighestRow();
            $highestColumnLetter = $worksheet->getHighestColumn();
            $highestColumnIndex  = Coordinate::columnIndexFromString($highestColumnLetter);

            if ($highestRow < 2) {
                return response()->json([
                    'error'   => 'Le fichier est vide.',
                    'details' => 'Le document ne contient aucune donnée à importer.'
                ], 422);
            }

            $images = $this->extractImagesFromExcel($worksheet);

            $telEcole = '00000000';
            if ($request->filled('ecole_id')) {
                $ecolePreview = Ecole::find($request->ecole_id);
                $telEcole     = $ecolePreview->telephone ?? '00000000';
            }

            $mappingRules = [
                'nom_prenom'   => ['nom et prenoms', 'nom & prenoms', 'nom prenoms', 'nom et prenom'],
                'matricule'    => ['matricule', 'identifiant', 'n° edumaster', 'edumaster'],
                'numero_table' => ['n° table', 'numero de table', 'num table', 'n°table', 'n° de table', 'numero table'],
                'sexe'         => ['sexe', 'genre', 'm/f', 'gender'],
                'date_lieu'    => ['date/lieu', 'date et lieu', 'date & lieu', 'date/lieu naissance'],
                'prenom'       => ['prenom', 'prenoms', 'prénom', 'prenom(s)'],
                'nom'          => ['nom', 'candidat'],
                'date_naiss'   => ['date de naissance', 'date naissance', 'né le', 'date naiss', 'date'],
                'lieu_naiss'   => ['lieu de naissance', 'lieu naissance', 'lieu naiss', 'lieu'],
                'telephone'    => ['telephone', 'parent', 'contact', 'tuteur', 'téléphone', 'tel', 'tél'],
                'nationalite'  => ['nationalité', 'nation', 'pays'],
            ];

            $indices     = [];
            $headerRow   = null;
            $usedColumns = [];

            for ($row = 1; $row <= 15; $row++) {
                $rowIndices  = [];
                $matchCount  = 0;
                $usedColumns = [];

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $colLetter = Coordinate::stringFromColumnIndex($col);
                    $cellValue = $worksheet->getCell($colLetter . $row)->getValue();
                    $val       = strtolower(Str::ascii(trim((string)$cellValue)));

                    if (empty($val) || $val === 'n°' || $val === 'no') continue;

                    foreach ($mappingRules as $field => $synonyms) {
                        foreach ($synonyms as $synonym) {
                            if (strlen($synonym) < 2 && $synonym !== 'm/f') continue;
                            if (in_array($colLetter, $usedColumns)) continue;

                            if ($val === $synonym || (strlen($synonym) > 2 && str_contains($val, $synonym))) {
                                if ($field === 'sexe' && isset($rowIndices['prenom']) && $rowIndices['prenom'] === $colLetter) {
                                    continue;
                                }
                                if (!isset($rowIndices[$field])) {
                                    $rowIndices[$field] = $colLetter;
                                    $usedColumns[]      = $colLetter;
                                    $matchCount++;
                                }
                                break;
                            }
                        }
                    }
                }

                if ($matchCount >= 3) {
                    $indices   = $rowIndices;
                    $headerRow = $row;
                    break;
                }
            }

            if (!$headerRow) {
                return response()->json([
                    'error'   => 'Colonnes non détectées.',
                    'details' => 'Assurez-vous que votre fichier contient des en-têtes clairs.'
                ], 422);
            }

            $students = [];
            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {

                // getFormattedValue() pour éviter les floats sur grands nombres
                $getVal = function ($field) use ($worksheet, $indices, $row) {
                    if (!isset($indices[$field])) return '';
                    $cell = $worksheet->getCell($indices[$field] . $row);
                    return trim((string) $cell->getFormattedValue());
                };

                $getRaw = function ($field) use ($worksheet, $indices, $row) {
                    if (!isset($indices[$field])) return '';
                    return $worksheet->getCell($indices[$field] . $row)->getValue();
                };

                $nom    = '';
                $prenom = '';

                if (isset($indices['nom_prenom'])) {
                    $full   = $this->cleanVal($getVal('nom_prenom'));
                    $parts  = explode(' ', $full, 2);
                    $nom    = $parts[0] ?? '';
                    $prenom = $parts[1] ?? '';
                } else {
                    $nom    = $this->cleanVal($getVal('nom'));
                    $prenom = $this->cleanVal($getVal('prenom'));
                }

                if (empty($nom) || is_numeric($nom)) continue;

                $dateNaiss = null;
                $lieuNaiss = '';
                if (isset($indices['date_lieu'])) {
                    $raw = $getVal('date_lieu');
                    if (preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/', $raw, $m)) {
                        $dateNaiss = $this->formatExcelDate($getRaw('date_lieu'), $m[1]);
                        $lieuNaiss = $this->cleanVal(trim(str_replace($m[1], '', $raw)));
                    }
                } else {
                    $dateNaiss = $this->formatExcelDate($getRaw('date_naiss'), $getVal('date_naiss'));
                    $lieuNaiss = $this->cleanVal($getVal('lieu_naiss'));
                }

                $rawSexe = $getVal('sexe');
                $s       = strtoupper(trim(Str::ascii($rawSexe)));
                $sexe    = 'M';
                if (in_array($s, ['F', 'FEMININ', 'FILLE', 'FEMME'])) {
                    $sexe = 'F';
                } elseif (strlen($s) > 0 && substr($s, 0, 1) === 'F') {
                    $sexe = 'F';
                }

                // Matricule — getFormattedValue pour éviter float
                $matricule = null;
                if (isset($indices['matricule'])) {
                    $rawMatricule = trim((string) $worksheet->getCell($indices['matricule'] . $row)->getFormattedValue());
                    $rawMatricule = preg_replace('/\s+/', '', $rawMatricule);
                    if (!empty($rawMatricule) && strlen($rawMatricule) >= 3) {
                        $matricule = $rawMatricule;
                    }
                }

                // Numéro de table — null si absent
                $numeroTable = null;
                if (isset($indices['numero_table'])) {
                    $rawNumTable = trim((string) $worksheet->getCell($indices['numero_table'] . $row)->getFormattedValue());
                    $rawNumTable = preg_replace('/\s+/', '', $rawNumTable);
                    if (!empty($rawNumTable)) {
                        $numeroTable = $rawNumTable;
                    }
                }

                $telRaw    = $getVal('telephone');
                $telephone = (!empty($telRaw) && $telRaw !== '00000000')
                    ? $telRaw
                    : $telEcole;

                $students[] = [
                    'photo'            => $images[$row] ?? null,
                    'matricule'        => $matricule,
                    'numero_table'     => $numeroTable,
                    'nom'              => strtoupper($nom),
                    'prenom'           => ucwords(strtolower($prenom)),
                    'sexe'             => $sexe,
                    'nationalite'      => $this->cleanVal($getVal('nationalite')) ?: 'BENIN',
                    'date_naissance'   => $dateNaiss,
                    'lieu_naissance'   => $lieuNaiss ?: '',
                    'telephone_tuteur' => $telephone,
                ];
            }

            if (empty($students)) {
                return response()->json([
                    'error'   => 'Aucune donnée d\'élève trouvée.',
                    'details' => 'Aucune ligne valide détectée.'
                ], 422);
            }

            return response()->json(['students' => $students]);

        } catch (Exception $e) {
            Log::error('Erreur Preview Import Admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse du fichier.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function storeAll(Request $request)
    {
        $request->validate([
            'ecole_id'  => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
            'students'  => 'required|array|min:1',
        ]);

        $ecole = Ecole::findOrFail($request->ecole_id);

        $successCount = 0;
        $errorCount   = 0;

        // Normalisation matricules
        $studentsNormalized = collect($request->students)->map(function ($s) {
            $s['matricule'] = !empty($s['matricule'])
                ? preg_replace('/\s+/', '', trim((string) $s['matricule']))
                : null;
            // Nettoyage _x000d_ sur tous les champs texte
            foreach (['nom', 'prenom', 'lieu_naissance'] as $field) {
                if (!empty($s[$field])) {
                    $s[$field] = preg_replace('/_x000[Dd]_/u', ' ', $s[$field]);
                    $s[$field] = trim(preg_replace('/\s{2,}/', ' ', $s[$field]));
                }
            }
            return $s;
        })->toArray();

        // Vérification doublons internes
        $matriculesDansFichier = collect($studentsNormalized)
            ->pluck('matricule')->filter()->values();

        $doublonsInternes = $matriculesDansFichier->duplicates()->values()->toArray();
        if (!empty($doublonsInternes)) {
            return response()->json([
                'success' => false,
                'message' => 'Le fichier contient des matricules en double : ' . implode(', ', $doublonsInternes),
                'type'    => 'duplicate_matricule',
            ], 422);
        }

        // Vérification doublons base
        if ($matriculesDansFichier->isNotEmpty()) {
            $existantsEnBase = Eleve::whereIn('matricule_edumaster', $matriculesDansFichier->toArray())
                ->pluck('matricule_edumaster')->toArray();
            if (!empty($existantsEnBase)) {
                return response()->json([
                    'success' => false,
                    'message' => count($existantsEnBase) . ' matricule(s) déjà enregistré(s) : ' . implode(', ', $existantsEnBase),
                    'type'    => 'duplicate_matricule',
                ], 422);
            }
        }

        try {
            DB::transaction(function () use ($studentsNormalized, $request, $ecole, &$successCount, &$errorCount) {
                foreach ($studentsNormalized as $index => $s) {

                    if (empty($s['nom']) || empty($s['prenom']) || empty($s['date_naissance'])) {
                        throw new \Exception('Données critiques manquantes ligne ' . ($index + 1));
                    }

                    $photoPath = null;
                    if (!empty($s['photo']) && preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type)) {
                        $data      = base64_decode(substr($s['photo'], strpos($s['photo'], ',') + 1));
                        $photoPath = 'eleves/photos/eleve_' . uniqid() . '.' . strtolower($type[1]);
                        Storage::disk('public')->put($photoPath, $data);
                    }

                    $qrContent  = 'Nom: ' . $s['nom'] . "\nPrenom: " . $s['prenom'] . "\nEducMaster: " . ($s['matricule'] ?? '');
                    $qrCodePath = 'eleves/qrcodes/' . Str::slug(($s['matricule'] ?: $s['nom'] . '_' . $index)) . '.png';
                    $qrFullPath = storage_path('app/public/' . $qrCodePath);

                    if (!file_exists(dirname($qrFullPath))) {
                        mkdir(dirname($qrFullPath), 0755, true);
                    }

                    $writer = new PngWriter();
                    $writer->write(new QrCode($qrContent))->saveToFile($qrFullPath);

                    $classe   = Classe::find($request->classe_id);
                    $classeId = $classe->id;

                    if (!empty($request->serie)) {
                        $serie = Serie::where('nom', $request->serie)->first();
                        if ($serie) {
                            $resolved = Classe::where('serie_id', $serie->id)
                                ->where('nom', $classe->nom)->first();
                            if ($resolved) $classeId = $resolved->id;
                        }
                    }

                    $telephone = (!empty($s['telephone_tuteur']) && $s['telephone_tuteur'] !== '00000000')
                        ? $s['telephone_tuteur']
                        : ($ecole->telephone ?? '00000000');

                    Eleve::create([
                        'ecole_id'            => $ecole->id,
                        'classe_id'           => $classeId,
                        'nom'                 => $s['nom'],
                        'prenom'              => $s['prenom'],
                        'sexe'                => $s['sexe'],
                        'nationalite'         => $s['nationalite'] ?? 'BENIN',
                        'date_naissance'      => $s['date_naissance'],
                        'lieu_naissance'      => $s['lieu_naissance'] ?? '',
                        'telephone_tuteur'    => $telephone,
                        'photo'               => $photoPath,
                        'matricule_edumaster' => $s['matricule'] ?? null,
                        'numero_table'        => $s['numero_table'] ?? null,
                        'qr_code'             => $qrCodePath,
                    ]);

                    $successCount++;
                }
            });

            return response()->json([
                'success' => true,
                'message' => "$successCount élève(s) importé(s) avec succès.",
                'stats'   => compact('successCount', 'errorCount')
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur StoreAll Import Admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement des élèves.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function extractImagesFromExcel($worksheet)
    {
        $images = [];
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            $coordinates = $drawing->getCoordinates();
            if (preg_match('/[A-Z]+(\d+)/', $coordinates, $matches)) {
                $rowNumber = (int)$matches[1];
                $contents  = null;
                $mime      = null;

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $contents = ob_get_clean();
                    $mime     = $drawing->getMimeType();
                } else {
                    $path = $drawing->getPath();
                    if (file_exists($path)) {
                        $contents = file_get_contents($path);
                    } else {
                        $zipReader = fopen($path, 'r');
                        if ($zipReader) {
                            $contents = stream_get_contents($zipReader);
                            fclose($zipReader);
                        }
                    }
                    if ($contents) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mime  = $finfo->buffer($contents);
                    }
                }

                if ($contents && $mime) {
                    $images[$rowNumber] = 'data:' . $mime . ';base64,' . base64_encode($contents);
                }
            }
        }
        return $images;
    }

    private function formatExcelDate($rawValue, $formattedValue = null)
    {
        if (is_numeric($rawValue) && $rawValue > 0) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject($rawValue))->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        $value = $formattedValue ?? (string) $rawValue;
        if (empty($value)) return null;

        try {
            if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $value, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
            if (preg_match('/(\d{2})-(\d{2})-(\d{4})/', $value, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}