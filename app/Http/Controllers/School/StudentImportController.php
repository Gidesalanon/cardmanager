<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StudentImportController extends Controller
{
    public function create()
    {
        return view('school.eleves.import.create', [
            'activeYear' => SchoolYear::active()->firstOrFail(),
            'classes' => Classe::select('id', 'nom')->orderBy('nom')->get()->unique('nom')->values(),
        ]);
    }

    public function preview(Request $request)
    {
        $user = auth()->user();

        if (!$user->ecole) {
            return response()->json([
                'error' => "Vous devez d'abord créer votre école avant d'importer des élèves."
            ], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $spreadsheet = IOFactory::load($request->file('document')->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $highestColumnLetter = $worksheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumnLetter);

        if ($highestRow < 2) {
            return response()->json([
                'error' => 'Le fichier est vide.',
                'details' => 'Le document ne contient aucune donnée à importer.'
            ], 422);
        }

        $images = $this->extractImagesFromExcel($worksheet);

        $mappingRules = [
            'nom_prenom'  => ['nom et prenoms', 'nom & prenoms', 'nom prenoms', 'nom et prenom'],
            'matricule'   => ['n° table', 'numero de table', 'matricule', 'identifiant'],
            'sexe'        => ['sexe', 'genre', 'm/f', 'gender'], // Supprimé 's' qui cause le problème
            'date_lieu'   => ['date/lieu', 'date et lieu', 'date & lieu', 'date/lieu naissance'],
            'prenom'      => ['prenom', 'prenoms', 'prénom', 'prenom(s)'],
            'nom'         => ['nom', 'candidat'],
            'date_naiss'  => ['date de naissance', 'date naissance', 'né le', 'date naiss', 'date'],
            'lieu_naiss'  => ['lieu de naissance', 'lieu naissance', 'lieu naiss', 'lieu'],
            'telephone'   => ['telephone', 'parent', 'contact', 'tuteur', 'téléphone', 'tel', 'tél'],
            'nationalite' => ['nationalité', 'nation', 'pays'],
        ];

        $indices = [];
        $headerRow = null;
        $usedColumns = []; // Pour tracker les colonnes déjà assignées

        \Log::info('=== IMPORT DEBUG START ===');
        \Log::info('Highest Row: ' . $highestRow . ' | Highest Col: ' . $highestColumnLetter);

        for ($row = 1; $row <= 15; $row++) {
            $rowIndices = [];
            $matchCount = 0;
            $usedColumns = []; // Réinitialiser pour chaque ligne

            \Log::info("--- Scan row $row pour header ---");

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                $cellValue = $worksheet->getCell($colLetter . $row)->getValue();
                $val = strtolower(Str::ascii(trim((string)$cellValue)));

                if (empty($val) || $val === 'n°' || $val === 'no') continue;

                \Log::info("  Col $colLetter: raw='$cellValue' | normalized='$val'");

                foreach ($mappingRules as $field => $synonyms) {
                    foreach ($synonyms as $synonym) {
                        // Éviter les correspondances trop courtes (comme 's')
                        if (strlen($synonym) < 2 && $synonym !== 'm/f') continue;

                        // Vérifier si la colonne est déjà utilisée
                        if (in_array($colLetter, $usedColumns)) continue;

                        // Correspondance exacte ou partielle pour les termes significatifs
                        if ($val === $synonym || (strlen($synonym) > 2 && str_contains($val, $synonym))) {
                            // Priorité: si on a déjà trouvé 'prenom' pour cette colonne, ne pas assigner 'sexe'
                            if ($field === 'sexe' && isset($rowIndices['prenom']) && $rowIndices['prenom'] === $colLetter) {
                                continue;
                            }

                            if (!isset($rowIndices[$field])) {
                                $rowIndices[$field] = $colLetter;
                                $usedColumns[] = $colLetter;
                                $matchCount++;
                                \Log::info("  => MATCH: field='$field' col='$colLetter' synonym='$synonym'");
                            }
                            break;
                        }
                    }
                }
            }

            \Log::info("Row $row: matchCount=$matchCount | indices=" . json_encode($rowIndices));

            if ($matchCount >= 3) {
                $indices = $rowIndices;
                $headerRow = $row;
                break;
            }
        }

        \Log::info('Header row final: ' . ($headerRow ?? 'NON DÉTECTÉ'));
        \Log::info('Indices finaux: ' . json_encode($indices));

        if (!$headerRow) {
            return response()->json([
                'error' => 'Colonnes non détectées.',
                'details' => 'Assurez-vous que votre fichier contient des en-têtes clairs (Nom, Prénom, Sexe, Date naissance, Lieu naissance, Téléphone parent).'
            ], 422);
        }

        $students = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {

            $getVal = function ($field) use ($worksheet, $indices, $row) {
                return isset($indices[$field]) ? trim((string)$worksheet->getCell($indices[$field] . $row)->getValue()) : '';
            };

            $nom = "";
            $prenom = "";

            if (isset($indices['nom_prenom'])) {
                $full = $getVal('nom_prenom');
                $parts = explode(' ', $full, 2);
                $nom = $parts[0] ?? '';
                $prenom = $parts[1] ?? '';
            } else {
                $nom = $getVal('nom');
                $prenom = $getVal('prenom');
            }

            if (empty($nom) || is_numeric($nom)) continue;

            $dateNaiss = null;
            $lieuNaiss = "";
            if (isset($indices['date_lieu'])) {
                $raw = $getVal('date_lieu');
                if (preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/', $raw, $m)) {
                    $dateNaiss = $this->formatExcelDate($m[1]);
                    $lieuNaiss = trim(str_replace($m[1], '', $raw));
                }
            } else {
                $dateNaiss = $this->formatExcelDate($getVal('date_naiss'));
                $lieuNaiss = $getVal('lieu_naiss');
            }

            // Récupération et interprétation correcte du sexe
            $rawSexe = $getVal('sexe');

            // Si on n'a pas trouvé de colonne sexe, essayer de détecter depuis le prénom (fallback)
            if (empty($rawSexe) && !empty($prenom)) {
                // Fallback: essayer de déduire le sexe du prénom? À éviter, mieux vaut avoir une colonne dédiée
                $rawSexe = '';
            }

            \Log::info("Row $row - Nom: '$nom' | Sexe RAW: '$rawSexe' | ASCII+Upper: '" . strtoupper(Str::ascii($rawSexe)) . "'");

            // Interprétation stricte du sexe
            $sexe = 'M'; // Valeur par défaut

            if (!empty($rawSexe)) {
                $s = strtoupper(trim(Str::ascii($rawSexe)));

                // Ne garder que le premier caractère si c'est M ou F
                if (str_starts_with($s, 'M') || str_starts_with($s, 'MASCULIN') || $s === 'M') {
                    $sexe = 'M';
                } elseif (str_starts_with($s, 'F') || str_starts_with($s, 'FEMININ') || $s === 'F') {
                    $sexe = 'F';
                } else {
                    // Vérifier si c'est un prénom (comme dans vos logs)
                    // Si la valeur est longue (>3 caractères) 
                    if (strlen($s) > 3 && !in_array($s, ['M', 'F', 'MASCULIN', 'FEMININ'])) {
                        \Log::info("Row $row - Valeur '$rawSexe' ignorée pour sexe (probablement un prénom)");
                        $sexe = 'M'; // Défaut
                    }
                }
            }

            \Log::info("Row $row - Sexe final: '$sexe'");

            $matricule = $getVal('matricule');
            if (empty($matricule) || strlen($matricule) < 3) {
                $matricule = "ID-" . strtoupper(substr(Str::slug($nom), 0, 3)) . "-" . $row;
            }

            $students[] = [
                'photo'            => $images[$row] ?? null,
                'matricule'        => $matricule,
                'nom'              => strtoupper($nom),
                'prenom'           => ucwords(strtolower($prenom)),
                'sexe'             => $sexe,
                'nationalite'      => $getVal('nationalite') ?: 'BENIN',
                'date_naissance'   => $dateNaiss,
                'lieu_naissance'   => $lieuNaiss ?: '',
                'telephone_tuteur' => $getVal('telephone') ?: '00000000',
            ];
        }

        \Log::info('Total students: ' . count($students));
        \Log::info('Premier étudiant: ' . json_encode($students[0] ?? null));
        \Log::info(' IMPORT DEBUG END');

        if (empty($students)) {
            return response()->json([
                'error' => 'Aucune donnée d\'élève trouvée.',
                'details' => 'Le fichier contient des en-têtes mais aucune ligne d\'élève valide n\'a été détectée en dessous.'
            ], 422);
        }

        return response()->json(['students' => $students]);
    }


    private function extractImagesFromExcel($worksheet)
    {
        $images = [];
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            $coordinates = $drawing->getCoordinates();
            if (preg_match('/(\d+)/', $coordinates, $matches)) {
                $rowNumber = (int)$matches[1];
                $contents = null;
                $mime = null;

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {
                    ob_start();
                    call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $contents = ob_get_clean();
                    $mime = $drawing->getMimeType();
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
                        $mime = $finfo->buffer($contents);
                    }
                }

                if ($contents && $mime) {
                    $images[$rowNumber] = 'data:' . $mime . ';base64,' . base64_encode($contents);
                }
            }
        }
        return $images;
    }

    private function formatExcelDate($value)
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) return Carbon::instance(Date::excelToDateTimeObject($value))->format('Y-m-d');
            if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $value, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }



    public function storeAll(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'students'  => 'required|array|min:1',
        ]);

        $ecole = Auth::user()->ecole;
        abort_if(!$ecole, 403);

        DB::transaction(function () use ($request, $ecole) {
            foreach ($request->students as $index => $s) {
                if (empty($s['nom']) || empty($s['prenom']) || empty($s['date_naissance'])) {
                    throw new \Exception("Données critiques manquantes ligne " . ($index + 1));
                }

                if (!empty($s['matricule']) && Eleve::where('matricule_edumaster', $s['matricule'])->exists()) {
                    continue;
                }

                $photoPath = null;
                if (!empty($s['photo']) && preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type)) {
                    $data = base64_decode(substr($s['photo'], strpos($s['photo'], ',') + 1));
                    $photoPath = 'eleves/photos/eleve_' . uniqid() . '.' . strtolower($type[1]);
                    Storage::disk('public')->put($photoPath, $data);
                }

                $qrContent = $s['matricule'] ?: $s['nom'] . '_' . $s['prenom'] . '_' . $index;
                $qrCodePath = 'eleves/qrcodes/' . Str::slug($qrContent) . '.png';

                Eleve::create([
                    'ecole_id' => $ecole->id,
                    'classe_id' => $request->classe_id,
                    'nom' => $s['nom'],
                    'prenom' => $s['prenom'],
                    'sexe' => $s['sexe'],
                    'nationalite' => $s['nationalite'] ?? 'BENIN',
                    'date_naissance' => $s['date_naissance'],
                    'lieu_naissance' => $s['lieu_naissance'] ?? '',
                    'telephone_tuteur' => $s['telephone_tuteur'] ?? '',
                    'photo' => $photoPath,
                    'matricule_edumaster' => $s['matricule'] ?? null,
                    'qr_code' => $qrCodePath,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
