<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MempParser extends BaseParser
{
    public function canHandle(Worksheet $worksheet): bool
    {
        // Vérifier la présence d'en-têtes spécifiques au MEMP
        $headers = ['Numéro de Table', 'Photo', 'Nom', 'Prénom(s)', 'Date de naissance', 'Lieu de naissance', 'Nationalité'];
        
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
            
            if ($foundHeaders >= 4) { // Au moins 4 en-têtes trouvés
                return true;
            }
        }
        
        return false;
    }

    public function getHeaderRow(): int
    {
        return 3; // Ligne d'en-tête pour CEP/MEMP (ligne 3 dans le fichier)
    }
    
public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData
{
    $numeroTable = trim((string) $this->getCellValue($worksheet, 'C' . $row));
    $nom = trim((string) $this->getCellValue($worksheet, 'D' . $row));
    $prenom = trim((string) $this->getCellValue($worksheet, 'E' . $row));

    // Ignorer uniquement si nom ET prénom sont vides
    if (empty($nom) && empty($prenom)) {
        return null;
    }

    $photo = $images[$row] ?? null;
    $sexe = trim((string) $this->getCellValue($worksheet, 'F' . $row));
    $nationalite = trim((string) $this->getCellValue($worksheet, 'G' . $row));
    $dateNaissance = $this->convertExcelDate(
        $this->getCellValue($worksheet, 'H' . $row)
    );
    $lieuNaissance = trim((string) $this->getCellValue($worksheet, 'I' . $row));
    
    // Vérifier si la colonne J contient bien les téléphones parents
    $headerJ = trim((string) $this->getCellValue($worksheet, 'J' . $this->getHeaderRow()));
    $telephoneParents = null;
    if (str_contains(strtolower($headerJ), 'téléphone') || str_contains(strtolower($headerJ), 'telephone')) {
        $telephoneParents = trim((string) $this->getCellValue($worksheet, 'J' . $row));
    }
    
    $collegesText = trim((string) $this->getCellValue($worksheet, 'K' . $row));

    // Normaliser sexe correctement
    $sexeNormalise = 'M';
    if (strtoupper($sexe) === 'F' || str_contains(strtoupper($sexe), 'FEM')) {
        $sexeNormalise = 'F';
    }

    // Parser les collèges
    $collegesChoisis = [];
    if (!empty($collegesText)) {
        preg_match_all('/\d+\.\s*([^0-9]+)/', $collegesText, $matches);
        $collegesChoisis = $matches[1] ?? [];
    }

    return new StudentData(
        photo: $photo,
        matricule: $numeroTable,
        nom: $nom,
        prenom: $prenom,
        sexe: $sexeNormalise,
        dateNaissance: $dateNaissance,
        lieuNaissance: $lieuNaissance,
        nationalite: $nationalite,
        telephoneTuteur: $telephoneParents,
        numeroTable: $numeroTable,
        collegesChoisis: $collegesChoisis,
    );
}

}
