<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapParser extends BaseParser
{
    public function canHandle(Worksheet $worksheet): bool
    {
        // Vérifier la présence d'en-têtes spécifiques au CAP
        $headers = ['Photo', 'Nom et Prénoms', 'Sexe', 'Date/Lieu Naissance', 'Etablissement', 'EPS'];
        
        $highestRow = $worksheet->getHighestRow();
        for ($row = 1; $row <= min($highestRow, 10); $row++) {
            $rowHeaders = [];
            $highestColumn = $worksheet->getHighestColumn();
            
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $this->getCellValue($worksheet, $col . $row);
                if (!empty($value)) {
                    $rowHeaders[] = strtolower($value);
                }
            }
            
            $foundHeaders = 0;
            foreach ($headers as $header) {
                if (in_array(strtolower($header), $rowHeaders)) {
                    $foundHeaders++;
                }
            }
            
            if ($foundHeaders >= 3) { // Au moins 3 en-têtes trouvés
                return true;
            }
        }
        
        return false;
    }

  public function getHeaderRow(): int
{
    return 2; // Ligne réelle d’en-tête
}

public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData
{
    $nom = trim($this->getCellValue($worksheet, 'E' . $row));
    $prenom = trim($this->getCellValue($worksheet, 'F' . $row));

    // Si ligne vide ou ligne d'en-tête
    if (empty($nom) || strtolower($nom) === 'nom') {
        return null;
    }

    $photo = $images[$row] ?? null;

    $sexe = strtoupper(trim($this->getCellValue($worksheet, 'G' . $row)));
    $dateNaissance = $this->convertExcelDate(
        $this->getCellValue($worksheet, 'H' . $row)
    );
    $lieuNaissance = trim($this->getCellValue($worksheet, 'I' . $row));

    $eps = trim($this->getCellValue($worksheet, 'K' . $row));
    $option = trim($this->getCellValue($worksheet, 'L' . $row));

    // Normalisation du sexe
    if ($sexe !== 'F') {
        $sexe = 'M';
    }

    return new StudentData(
        photo: $photo,
        matricule: null,
        nom: $nom,
        prenom: $prenom,
        sexe: $sexe,
        dateNaissance: $dateNaissance,
        lieuNaissance: $lieuNaissance,
        etablissement: null,
        eps: $eps,
        observation: $option,
    );
}

}
