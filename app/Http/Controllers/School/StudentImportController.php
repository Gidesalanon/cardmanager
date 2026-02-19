<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class StudentImportController extends Controller
{
    public function create()
    {
        return view('school.eleves.import.create', [
            'activeYear' => SchoolYear::active()->firstOrFail(),
            'classes' => Classe::select('id', 'nom')
                ->orderBy('nom')
                ->get()
                ->unique('nom')
                ->values(),
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        // Extraire les images du fichier Excel
        $images = $this->extractImagesFromExcel($request->file('document'));
        
        // Debug temporaire
        if (empty($images)) {
            return response()->json(['debug' => 'Aucune image trouvée dans le fichier Excel']);
        }

        // Utiliser PhpSpreadsheet directement pour éviter le décalage
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('document')->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $students = [];
        $seenMatricules = [];

        // Commencer à la ligne 4 (premier élève)
        for ($row = 4; $row <= $highestRow; $row++) {

            $matricule = trim((string) $worksheet->getCell("C{$row}")->getValue());

            if (empty($matricule) || str_contains(strtolower($matricule), 'matricule')) {
                continue;
            }

            if (in_array($matricule, $seenMatricules)) {
                continue;
            }
            $seenMatricules[] = $matricule;

            // Récupérer les autres colonnes
            $nom = trim($worksheet->getCell("D{$row}")->getValue() ?? '');
            $prenom = trim($worksheet->getCell("E{$row}")->getValue() ?? '');
            $rawSexe = strtoupper(trim((string)($worksheet->getCell("F{$row}")->getValue() ?? '')));
            $nationalite = trim($worksheet->getCell("G{$row}")->getValue() ?? '');
            $dateCell = $worksheet->getCell("H{$row}")->getValue();
            $lieuNaissance = trim($worksheet->getCell("I{$row}")->getValue() ?? '');
            $telephone = trim($worksheet->getCell("J{$row}")->getValue() ?? '');

            // Gestion du sexe
            $sexe = 'M';
            if ($rawSexe === 'F' || str_contains($rawSexe, 'FEM')) {
                $sexe = 'F';
            }

            // Gestion de la date
            $dateNaissance = null;
            if (!empty($dateCell)) {
                try {
                    if (is_numeric($dateCell)) {
                        $dateNaissance = Carbon::instance(
                            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateCell)
                        )->format('Y-m-d');
                    } else {
                        $dateNaissance = Carbon::parse($dateCell)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $dateNaissance = null;
                }
            }

            // Récupérer la photo par numéro de ligne exact
            $photo = $images[$row] ?? null;

            $students[] = [
                'photo' => $photo,
                'matricule' => $matricule,
                'nom' => $nom,
                'prenom' => $prenom,
                'sexe' => $sexe,
                'nationalite' => $nationalite,
                'date_naissance' => $dateNaissance,
                'lieu_naissance' => $lieuNaissance,
                'telephone_tuteur' => $telephone,
            ];
        }

        return response()->json(['students' => $students]);
    }

    private function extractImagesFromExcel($file)
    {
        $images = [];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            \Log::info('Début extraction images - Nombre de dessins: ' . count($worksheet->getDrawingCollection()));

            foreach ($worksheet->getDrawingCollection() as $drawing) {

                $coordinates = $drawing->getCoordinates(); // ex: B4
                preg_match('/\d+/', $coordinates, $matches);
                $rowNumber = $matches[0] ?? null;

                \Log::info('Image trouvée - Coordonnées: ' . $coordinates . ', Ligne: ' . $rowNumber);

                if (!$rowNumber) continue;

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {

                    ob_start();
                    call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $imageContents = ob_get_clean();

                    $mimeType = image_type_to_mime_type($drawing->getMimeType());

                    $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);

                    \Log::info('Image MemoryDrawing traitée - Ligne: ' . $rowNumber);

                } elseif ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\Drawing) {

                    $imagePath = $drawing->getPath();

                    // Gérer les chemins ZIP (Excel temporaire)
                    if (str_contains($imagePath, 'zip://')) {
                        try {
                            // Extraire l'image du ZIP
                            $imageContents = file_get_contents($imagePath);
                            if ($imageContents !== false) {
                                // Détecter le MIME type depuis le contenu
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mimeType = finfo_buffer($finfo, $imageContents);
                                finfo_close($finfo);
                                 
                                $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                                 
                                \Log::info('Image ZIP traitée - Ligne: ' . $rowNumber . ', Taille: ' . strlen($imageContents) . ' bytes');
                            } else {
                                \Log::warning('Image ZIP non lue - Ligne: ' . $rowNumber . ', Chemin: ' . $imagePath);
                            }
                        } catch (\Exception $e) {
                            \Log::error('Erreur extraction ZIP - Ligne: ' . $rowNumber . ', Erreur: ' . $e->getMessage());
                        }
                    } elseif (file_exists($imagePath)) {
                        $imageContents = file_get_contents($imagePath);
                        $mimeType = mime_content_type($imagePath);

                        $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);

                        \Log::info('Image Drawing traitée - Ligne: ' . $rowNumber . ', Chemin: ' . $imagePath);
                    } else {
                        \Log::warning('Image Drawing non trouvée - Ligne: ' . $rowNumber . ', Chemin: ' . $imagePath);
                    }
                }
            }

            \Log::info('Extraction terminée - Images extraites: ' . count($images));

        } catch (\Exception $e) {
            \Log::error('Error extracting images: ' . $e->getMessage());
        }

        return $images;
    }

    public function storeAll(Request $request)
    {
        $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'students'  => 'required|array|min:1',
        ]);

        $ecole = auth()->user()->ecole;
        abort_if(!$ecole, 403);

        DB::transaction(function () use ($request, $ecole) {

            foreach ($request->students as $index => $s) {

                if (
                    empty($s['photo']) ||
                    empty($s['matricule']) ||
                    empty($s['nom']) ||
                    empty($s['prenom']) ||
                    empty($s['sexe']) ||
                    empty($s['date_naissance']) ||
                    empty($s['lieu_naissance']) ||
                    empty($s['telephone_tuteur'])
                ) {
                    throw new \Exception("Champs manquants ligne " . ($index+1));
                }

                if (Eleve::where('matricule_edumaster', $s['matricule'])->exists()) {
                    continue;
                }

                $photoPath = null;

                if (preg_match('/^data:image\/(\w+);base64,/', $s['photo'], $type)) {

                    $data = substr($s['photo'], strpos($s['photo'], ',') + 1);
                    $data = base64_decode($data);

                    $extension = strtolower($type[1]);
                    $fileName = uniqid('eleve_').'.'.$extension;

                    $photoPath = 'eleves/photos/'.$fileName;

                    Storage::disk('public')->put($photoPath, $data);
                }

                $matricule = $s['matricule'];

                $qrCodePath = 'eleves/qrcodes/' . Str::slug($matricule) . '.png';
                $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);

                //  créer le dossier si inexistant
                $directory = dirname($qrCodeFullPath);

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $qrCode = new \Endroid\QrCode\QrCode($matricule);
                $writer = new \Endroid\QrCode\Writer\PngWriter();

                $result = $writer->write($qrCode);

                $result->saveToFile($qrCodeFullPath);

                Eleve::create([
                    'ecole_id' => $ecole->id,
                    'classe_id' => $request->classe_id,
                    'nom' => $s['nom'],
                    'prenom' => $s['prenom'],
                    'sexe' => $s['sexe'],
                    'nationalite' => $s['nationalite'] ?? null,
                    'date_naissance' => $s['date_naissance'],
                    'lieu_naissance' => $s['lieu_naissance'],
                    'telephone_tuteur' => $s['telephone_tuteur'],
                    'photo' => $photoPath,
                    'matricule_edumaster' => $matricule,
                    'qr_code' => $qrCodePath,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
