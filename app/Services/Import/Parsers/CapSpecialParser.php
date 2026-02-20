<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapSpecialParser extends BaseParser
{
    public function canHandle(Worksheet $worksheet): bool
    {
        // Vérifier la présence de "certificat d'aptitude professionnelle" ou "cap" dans les premières lignes
        $highestRow = $worksheet->getHighestRow();
        for ($row = 1; $row <= min($highestRow, 5); $row++) {
            $rowContent = '';
            $highestColumn = $worksheet->getHighestColumn();
            
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $value = $this->getCellValue($worksheet, $col . $row);
                if (!empty($value)) {
                    $rowContent .= strtolower($value) . ' ';
                }
            }
            
            // Vérifier si c'est un fichier CAP
            if (str_contains($rowContent, 'certificat d\'aptitude professionnelle') || 
                str_contains($rowContent, 'cap') ||
                str_contains($rowContent, 'aide-comptable')) {
                return true;
            }
        }
        
        return false;
    }

    public function getHeaderRow(): int
    {
        return 2; // Ligne réelle d'en-tête
    }
public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData
{
    // Générer matricule automatiquement
    $numero = (string)($row - 1);

    $nomPrenoms = trim($this->getCellValue($worksheet, 'B' . $row));
    $sexe = trim($this->getCellValue($worksheet, 'C' . $row));
    $dateLieuNaissance = trim($this->getCellValue($worksheet, 'D' . $row));
    $eps = trim($this->getCellValue($worksheet, 'E' . $row));
    $etablissement = trim($this->getCellValue($worksheet, 'F' . $row));
    $observation = trim($this->getCellValue($worksheet, 'H' . $row));

    // Photo FIX IMPORTANT
    $photo = $images[$row] ?? null;

    if (empty($nomPrenoms)) {
        return null;
    }

    /**
     * NOM / PRENOM
     */
    $parts = preg_split('/\s+/', trim($nomPrenoms));

    $nom = array_shift($parts);
    $prenom = implode(' ', $parts);

    /**
     * DATE / LIEU
     */
    $dateNaissance = null;
    $lieuNaissance = null;

    if (!empty($dateLieuNaissance)) {

        if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s*(.*)/', $dateLieuNaissance, $matches)) {

            $dateNaissance = $this->convertExcelDate($matches[1]);

            $lieuNaissance = strtoupper(trim($matches[2]));
        }
    }

    /**
     * SEXE
     */
    $sexeNormalise = strtoupper($sexe) === 'F' ? 'F' : 'M';

    return new StudentData(
        photo: $photo,
        matricule: $numero,
        nom: $nom,
        prenom: $prenom,
        sexe: $sexeNormalise,
        dateNaissance: $dateNaissance,
        lieuNaissance: $lieuNaissance,
        etablissement: $etablissement,
        eps: $eps,
        observation: $observation,
    );
}


}
