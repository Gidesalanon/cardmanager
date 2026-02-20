<?php

namespace App\Services\Import;

use App\Services\Import\Models\StudentData;
use App\Services\Import\Parsers\BaseParser;
use App\Services\Import\Parsers\CapParser;
use App\Services\Import\Parsers\CapSpecialParser;
use App\Services\Import\Parsers\BepcParser;
use App\Services\Import\Parsers\MempParser;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelStudentImporter
{
    private array $parsers = [];

    public function __construct()
    {
        $this->parsers = [
            new CapSpecialParser(),
            new CapParser(),
            new BepcParser(),
            new MempParser(),
        ];
    }

    public function import(string $filePath): array
    {
        \Log::info('=== DÉBUT IMPORT EXCEL ===');
        \Log::info('Fichier: ' . $filePath);
        
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Extraire les images
        \Log::info('Extraction des images...');
        $images = $this->extractImages($worksheet);
        \Log::info('Images extraites: ' . count($images));

        // Détecter le parser approprié
        \Log::info('Détection du parser...');
        $parser = $this->detectParser($worksheet);
        if (!$parser) {
            \Log::error('Aucun parser compatible trouvé');
            throw new \Exception('Aucun parser compatible trouvé pour ce fichier Excel');
        }

        \Log::info('Parser détecté: ' . get_class($parser));
        
        // Extraire les étudiants
        $students = [];
        $headerRow = $parser->getHeaderRow();
        $highestRow = $worksheet->getHighestRow();
        
        \Log::info("Ligne d'en-tête: $headerRow, Ligne maximale: $highestRow");

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            \Log::info("Traitement ligne: $row");
            $student = $parser->extractStudent($worksheet, $row, $images);
            if ($student) {
                $students[] = $student;
                \Log::info("Étudiant extrait: " . $student->nom . ' ' . $student->prenom);
            } else {
                \Log::info("Ligne $row ignorée (pas de données valides)");
            }
        }

        \Log::info('Total étudiants extraits: ' . count($students));
        \Log::info('=== FIN IMPORT EXCEL ===');

        return [
            'students' => $students,
            'parser_type' => get_class($parser),
            'header_row' => $headerRow,
            'total_rows' => $highestRow - $headerRow,
        ];
    }

    private function detectParser(Worksheet $worksheet): ?BaseParser
    {
        \Log::info('Test des parsers disponibles...');
        
        foreach ($this->parsers as $parser) {
            $parserClass = get_class($parser);
            \Log::info("Test du parser: $parserClass");
            
            if ($parser->canHandle($worksheet)) {
                \Log::info("✅ Parser accepté: $parserClass");
                return $parser;
            } else {
                \Log::info("❌ Parser rejeté: $parserClass");
            }
        }
        
        \Log::error('Aucun parser n\'a pu gérer ce fichier');
        return null;
    }

    private function extractImages(Worksheet $worksheet): array
    {
        $images = [];

        try {
            foreach ($worksheet->getDrawingCollection() as $drawing) {
                $coordinates = $drawing->getCoordinates();
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
                    
                    if (str_contains($imagePath, 'zip://')) {
                        try {
                            $imageContents = file_get_contents($imagePath);
                            if ($imageContents !== false) {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $mimeType = finfo_buffer($finfo, $imageContents);
                                finfo_close($finfo);
                                $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                            }
                        } catch (\Exception $e) {
                            
                        }
                    } elseif (file_exists($imagePath)) {
                        $imageContents = file_get_contents($imagePath);
                        $mimeType = mime_content_type($imagePath);
                        $images[$rowNumber] = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
                    }
                }
            }
        } catch (\Exception $e) {
           
        }

        return $images;
    }
}
