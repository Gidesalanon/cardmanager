<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseParser
{
    abstract public function canHandle(Worksheet $worksheet): bool;
    abstract public function getHeaderRow(): int;
    abstract public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData;

    protected function splitNameAndFirstName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);
        return [
            'nom' => $parts[0] ?? '',
            'prenom' => $parts[1] ?? '',
        ];
    }

    protected function splitDateAndPlace(string $datePlace): array
    {
        // Format: "22/11/2007 COTONOU"
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s+(.+)/', $datePlace, $matches)) {
            return [
                'date' => $matches[1],
                'lieu' => $matches[2],
            ];
        }
        
        return [
            'date' => '',
            'lieu' => $datePlace,
        ];
    }

    protected function convertExcelDate($excelDate): ?string
    {
        if (empty($excelDate)) return null;
        
        try {
            if (is_numeric($excelDate)) {
                return \Carbon\Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate)
                )->format('Y-m-d');
            } else {
                // Essayer d'abord le format français JJ/MM/AAAA
                $date = \DateTime::createFromFormat('d/m/Y', $excelDate);
                if ($date) {
                    return $date->format('Y-m-d');
                }
                
                // Si ça échoue, essayer avec Carbon pour d'autres formats
                return \Carbon\Carbon::parse($excelDate)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getCellValue(Worksheet $worksheet, string $cell): string
    {
        return trim((string) $worksheet->getCell($cell)->getValue() ?? '');
    }

    protected function findHeaderRow(Worksheet $worksheet, array $possibleHeaders): int
    {
        $highestRow = $worksheet->getHighestRow();
        
        for ($row = 1; $row <= min($highestRow, 10); $row++) {
            $rowValues = [];
            $highestColumn = $worksheet->getHighestColumn();
            
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $this->getCellValue($worksheet, $col . $row);
                if (!empty($value)) {
                    $rowValues[] = strtolower($value);
                }
            }
            
            foreach ($possibleHeaders as $header) {
                if (in_array(strtolower($header), $rowValues)) {
                    return $row;
                }
            }
        }
        
        return 4; // Default fallback
    }
}
