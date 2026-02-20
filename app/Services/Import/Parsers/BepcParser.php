<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BepcParser extends BaseParser
{
    public function canHandle(Worksheet $worksheet): bool
    {
        // Vérifier la présence d'en-têtes SPÉCIFIQUES au BEPC (pas dans CEP)
        $headers = ['N° Table', 'Photo', 'Nom', 'Prénom(s)', 'Date de naissance', 'Lieu de naissance', 'Salle', 'Option'];
        
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
            
            \Log::info("BepcParser - En-têtes ligne $row: " . implode(', ', $rowHeaders));
            
            $foundHeaders = 0;
            foreach ($headers as $header) {
                if (in_array(strtolower($header), $rowHeaders)) {
                    $foundHeaders++;
                }
            }
            
            // Exiger AU MOINS 6 en-têtes ET au moins un en-tête spécifique BEPC (Salle ou Option)
            $hasSpecificBepcHeader = in_array('salle', $rowHeaders) || in_array('option', $rowHeaders);
            
            if ($foundHeaders >= 6 && $hasSpecificBepcHeader) {
                \Log::info("BepcParser - ✅ Accepté (ligne $row, $foundHeaders/8 en-têtes, avec spécifique BEPC)");
                return true;
            }
        }
        
        \Log::info("BepcParser - ❌ Rejeté (pas assez d'en-têtes spécifiques BEPC)");
        return false;
    }

    public function getHeaderRow(): int
    {
        return 3; // Ligne d'en-tête pour BEPC (ligne 3 dans le fichier)
    }

   public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData
{
    $numeroTable = trim($this->getCellValue($worksheet, 'C' . $row));

    if (empty($numeroTable) || stripos($numeroTable, 'n°') !== false) {
        return null;
    }

    $photo = $images[$row] ?? null;

    $nom = trim($this->getCellValue($worksheet, 'D' . $row));
    $prenom = trim($this->getCellValue($worksheet, 'E' . $row));
    $sexeRaw = trim($this->getCellValue($worksheet, 'F' . $row));
    $dateNaissance = $this->convertExcelDate(
        $this->getCellValue($worksheet, 'H' . $row) // ⚠️ corrigé ici
    );
    $lieuNaissance = trim($this->getCellValue($worksheet, 'I' . $row));
    $telephoneParent = trim($this->getCellValue($worksheet, 'J' . $row));

    // Normalisation sexe correcte
    $sexe = 'M';
    if (strtoupper($sexeRaw) === 'F' || str_contains(strtoupper($sexeRaw), 'FEM')) {
        $sexe = 'F';
    }

    return new StudentData(
        photo: $photo,
        matricule: $numeroTable,
        nom: $nom,
        prenom: $prenom,
        sexe: $sexe,
        dateNaissance: $dateNaissance,
        lieuNaissance: $lieuNaissance,
        telephoneTuteur: $telephoneParent,
        numeroTable: $numeroTable,
    );
}

}
