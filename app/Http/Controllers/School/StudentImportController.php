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
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
    $request->validate([
        'document' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    $spreadsheet = IOFactory::load($request->file('document')->getPathname());
    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();

    $images = $this->extractImagesFromExcel($worksheet);

    // Mapping plus précis pour éviter les conflits entre 'nom' et 'nom_prenom'
    $mappingRules = [
        'nom_prenom'  => ['nom et prenoms', 'nom & prenoms', 'nom prenoms', 'nom et prenom'],
        'matricule'   => ['n° table', 'numero de table', 'matricule', 'numéro de table', 'n°'],
        'sexe'        => ['sexe', 'genre', 'sex'],
        'date_lieu'   => ['date/lieu', 'date et lieu', 'date & lieu', 'date/lieu naissance'],
        'nom'         => ['nom', 'candidat'],
        'prenom'      => ['prenom', 'prenoms', 'prénom'],
        'date_naiss'  => ['date de naissance', 'né le', 'date naiss'],
        'lieu_naiss'  => ['lieu de naissance', 'lieu naiss'],
        'telephone'   => ['telephone', 'parent', 'contact', 'tuteur', 'téléphone'],
    ];

    $indices = [];
    $headerRow = null;

    for ($row = 1; $row <= 15; $row++) {
        $rowIndices = [];
        $matchCount = 0;
        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $cellValue = $worksheet->getCell($col . $row)->getValue();
            $val = strtolower(Str::ascii(trim((string)$cellValue)));
            if (empty($val)) continue;

            foreach ($mappingRules as $field => $synonyms) {
                foreach ($synonyms as $synonym) {
                    // Match exact ou contient (pour les noms composés)
                    if ($val === $synonym || (strlen($val) >= 4 && str_contains($val, $synonym))) {
                        if (!isset($rowIndices[$field])) {
                            $rowIndices[$field] = $col;
                            $matchCount++;
                        }
                        break;
                    }
                }
            }
        }
        // On valide la ligne d'entête si on a au moins 3 colonnes clés
        if ($matchCount >= 3) {
            $indices = $rowIndices;
            $headerRow = $row;
            break;
        }
    }

    $students = [];
    $startRow = ($headerRow ?? 1) + 1;

    for ($row = $startRow; $row <= $highestRow; $row++) {
        $getVal = function($field) use ($worksheet, $indices, $row) {
            return isset($indices[$field]) ? trim((string)$worksheet->getCell($indices[$field] . $row)->getValue()) : '';
        };

        // --- Logique de séparation NOM / PRENOM ---
        $nom = "";
        $prenom = "";
        
        // On donne la priorité à la colonne combinée "Nom et Prénoms"
        if (isset($indices['nom_prenom'])) {
            $full = $getVal('nom_prenom');
            if (!empty($full)) {
                $parts = explode(' ', $full, 2);
                $nom = $parts[0] ?? '';
                $prenom = $parts[1] ?? '';
            }
        } else {
            $nom = $getVal('nom');
            $prenom = $getVal('prenom');
        }

        // Si la ligne est vide (souvent le cas en fin de tableau Excel), on ignore
        if (empty($nom) || is_numeric($nom)) continue;

        // --- Logique de séparation DATE / LIEU ---
        $dateNaiss = null; 
        $lieuNaiss = "";

        if (isset($indices['date_lieu'])) {
            $raw = $getVal('date_lieu');
            // Regex pour extraire JJ/MM/AAAA
            if (preg_match('/(\d{2}[\/\-]\d{2}[\/\-]\d{4})/', $raw, $m)) {
                $dateNaiss = $this->formatExcelDate($m[1]);
                $lieuNaiss = trim(str_replace($m[1], '', $raw));
            }
        } else {
            $dateNaiss = $this->formatExcelDate($getVal('date_naiss'));
            $lieuNaiss = $getVal('lieu_naiss');
        }

        $s = strtoupper(Str::ascii($getVal('sexe')));
        $sexe = (str_starts_with($s, 'F') || str_contains($s, 'FEM')) ? 'F' : 'M';

        $students[] = [
            'photo'            => $images[$row] ?? null, 
            'matricule'        => $getVal('matricule'), 
            'nom'              => strtoupper($nom),
            'prenom'           => ucwords(strtolower($prenom)),
            'sexe'             => $sexe,
            'nationalite'      => 'BENIN',
            'date_naissance'   => $dateNaiss,
            'lieu_naissance'   => $lieuNaiss ?: '',
            'telephone_tuteur' => $getVal('telephone'),
        ];
    }
    return response()->json(['students' => $students]);
}

    private function extractImagesFromExcel($worksheet)
    {
        $images = [];
        foreach ($worksheet->getDrawingCollection() as $drawing) {
            // On récupère la ligne à partir des coordonnées (ex: B4 -> 4)
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
                    // Pour les fichiers standard (Drawing)
                    $path = $drawing->getPath();
                    if (file_exists($path)) {
                        $contents = file_get_contents($path);
                    } else {
                        // Si le chemin est relatif à l'archive Excel
                        $zipReader = fopen($path, 'r');
                        if ($zipReader) {
                            $contents = stream_get_contents($zipReader);
                            fclose($zipReader);
                        }
                    }

                    // Détecter le mime type si possible
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
        } catch (\Exception $e) { return null; }
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
                // Validation minimale (Nom/Prénom/Sexe/Date sont critiques)
                if (empty($s['nom']) || empty($s['prenom']) || empty($s['date_naissance'])) {
                    throw new \Exception("Données critiques manquantes ligne " . ($index+1));
                }

                // Si matricule présent, on vérifie les doublons
                if (!empty($s['matricule']) && Eleve::where('matricule_edumaster', $s['matricule'])->exists()) {
                    continue;
                }

                $photoPath = null;
                if (!empty($s['photo']) && preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type)) {
                    $data = base64_decode(substr($s['photo'], strpos($s['photo'], ',') + 1));
                    $photoPath = 'eleves/photos/eleve_'.uniqid().'.'.strtolower($type[1]);
                    Storage::disk('public')->put($photoPath, $data);
                }

                // Génération QR Code uniquement si matricule existe, sinon on utilise le nom
                $qrContent = $s['matricule'] ?: $s['nom'].'_'.$s['prenom'].'_'.$index;
                $qrCodePath = 'eleves/qrcodes/' . Str::slug($qrContent) . '.png';
                
                // (Logique de sauvegarde QR Code ici...)

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
