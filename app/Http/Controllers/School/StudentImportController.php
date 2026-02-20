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
use App\Services\Import\ExcelStudentImporter;

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

        try {
            \Log::info('=== DÉBUT PREVIEW CONTROLLER ===');
            \Log::info('Fichier reçu: ' . $request->file('document')->getClientOriginalName());
            
            $importer = new ExcelStudentImporter();
            $result = $importer->import($request->file('document')->getPathname());

            \Log::info('Résultat import: ' . json_encode([
                'students_count' => count($result['students']),
                'parser_type' => $result['parser_type'],
                'header_row' => $result['header_row'],
                'total_rows' => $result['total_rows']
            ]));

            // Convertir les StudentData en format pour la vue
            $students = array_map(function ($student) {
                return $student->toArray();
            }, $result['students']);

            \Log::info('Nombre d\'étudiants convertis: ' . count($students));

            $response = [
                'students' => $students,
                'parser_type' => class_basename($result['parser_type']),
                'header_row' => $result['header_row'],
                'total_rows' => $result['total_rows'],
            ];

            \Log::info('Réponse JSON: ' . json_encode($response));
            \Log::info('=== FIN PREVIEW CONTROLLER ===');

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('ERREUR PREVIEW: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Erreur lors de l\'import: ' . $e->getMessage()
            ], 422);
        }
    }

    private function extractImagesFromExcel($file)
    {
        $images = [];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            foreach ($worksheet->getDrawingCollection() as $drawing) {

                $coordinates = $drawing->getCoordinates(); // ex: B4
                preg_match('/\d+/', $coordinates, $matches);
                $rowNumber = $matches[0] ?? null;

                if (!$rowNumber) continue;

                if ($drawing instanceof \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing) {

                    ob_start();
                    call_user_func($drawing->getRenderingFunction(), $drawing->getImageResource());
                    $imageContents = ob_get_clean();

                    $mimeType = image_type_to_mime_type($drawing->getMimeType());

                    $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);

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
                            }
                        } catch (\Exception $e) {
                            // Erreur silencieuse en production
                        }
                    } elseif (file_exists($imagePath)) {
                        $imageContents = file_get_contents($imagePath);
                        $mimeType = mime_content_type($imagePath);

                        $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                    }
                }
            }

        } catch (\Exception $e) {
            // Erreur silencieuse en production
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

            foreach ($request->students as $index => $studentData) {

                // Validation des champs requis
                \Log::info('Validation élève ligne ' . ($index+1) . ': ' . json_encode($studentData));
                
                $requiredFields = ['matricule', 'nom', 'prenom', 'sexe', 'date_naissance', 'lieu_naissance'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (!array_key_exists($field, $studentData)) {
                        $missingFields[] = $field;
                    }
                }
                
                if (!empty($missingFields)) {
                    \Log::error('Champs manquants ligne ' . ($index+1) . ': ' . implode(', ', $missingFields));
                    throw new \Exception("Champs manquants ligne " . ($index+1) . ": " . implode(', ', $missingFields));
                }

                // Vérifier si l'élève existe déjà
                if (Eleve::where('matricule_edumaster', $studentData['matricule'])->exists()) {
                    continue;
                }

                // Gestion de la photo
                $photoPath = null;
                if (!empty($studentData['photo']) && preg_match('/^data:image\/(\w+);base64,/', $studentData['photo'], $type)) {
                    $data = substr($studentData['photo'], strpos($studentData['photo'], ',') + 1);
                    $data = base64_decode($data);
                    $extension = strtolower($type[1]);
                    $fileName = uniqid('eleve_').'.'.$extension;
                    $photoPath = 'eleves/photos/'.$fileName;
                    Storage::disk('public')->put($photoPath, $data);
                }

                // Génération du QR code
                $matricule = $studentData['matricule'];
                
                // Vérifier si le matricule est valide pour le QR code
                if (empty($matricule)) {
                    $matricule = 'MATRICULE_' . uniqid(); // Générer un matricule par défaut
                }
                
                $qrCodePath = 'eleves/qrcodes/' . Str::slug($matricule) . '.png';
                $qrCodeFullPath = storage_path('app/public/' . $qrCodePath);
                
                $directory = dirname($qrCodeFullPath);
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $qrCode = new \Endroid\QrCode\QrCode($matricule);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                $result->saveToFile($qrCodeFullPath);

                // Création de l'élève
                Eleve::create([
                    'ecole_id' => $ecole->id,
                    'classe_id' => $request->classe_id,
                    'nom' => $studentData['nom'],
                    'prenom' => $studentData['prenom'],
                    'sexe' => $studentData['sexe'],
                    'nationalite' => $studentData['nationalite'] ?? null,
                    'date_naissance' => $studentData['date_naissance'],
                    'lieu_naissance' => $studentData['lieu_naissance'],
                    'telephone_tuteur' => $studentData['telephone_tuteur'] ?? null,
                    'photo' => $photoPath,
                    'matricule_edumaster' => $matricule,
                    'qr_code' => $qrCodePath,
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}
