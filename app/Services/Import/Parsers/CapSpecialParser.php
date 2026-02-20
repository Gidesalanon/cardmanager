<?php

namespace App\Services\Import\Parsers;

use App\Services\Import\Models\StudentData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapSpecialParser extends BaseParser
{
    public function canHandle(Worksheet $worksheet): bool
    {
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
        return 2;
    }

    /**
     * Récupère la valeur d'une cellule en gérant les dates Excel (nombres sériels)
     */
    private function getCellValueFormatted(Worksheet $worksheet, string $cellCoord): string
    {
        $cell = $worksheet->getCell($cellCoord);
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            return '';
        }

        // Détecter si c'est une date Excel (nombre sériel)
        if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell) && is_numeric($value)) {
            $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return \Carbon\Carbon::instance($dateObj)->format('d/m/Y');
        }

        return trim((string) $value);
    }

    public function extractStudent(Worksheet $worksheet, int $row, array $images): ?StudentData
    {
        \Log::info("Traitement ligne: $row");

        // Récupérer le matricule depuis la colonne A
        $matricule = trim($this->getCellValue($worksheet, 'A' . $row));
        if (empty($matricule)) {
            $matricule = null;
        }

        $nomPrenoms        = trim($this->getCellValue($worksheet, 'B' . $row));
        $sexe              = trim($this->getCellValue($worksheet, 'C' . $row));
        
        // Utiliser getCellValueFormatted pour la colonne D (date + lieu)
        $dateLieuNaissance = $this->getCellValueFormatted($worksheet, 'D' . $row);
        
        $eps               = trim($this->getCellValue($worksheet, 'E' . $row));
        $etablissement     = trim($this->getCellValue($worksheet, 'F' . $row));
        $observation       = trim($this->getCellValue($worksheet, 'H' . $row));

        // -------------------------------------------------------
        // CORRECTION PHOTOS : cast en string pour matcher les clés
        // Les clés de $images sont des strings ("3", "5", etc.)
        // -------------------------------------------------------
        $rowKey = (string) $row;
        $photo = $images[$rowKey] 
              ?? $images[(string)($row - 1)] 
              ?? $images[(string)($row + 1)] 
              ?? null;

        \Log::info("Photo pour ligne $row (key: $rowKey): " . ($photo ? 'OUI (longueur: ' . strlen($photo) . ')' : 'NON'));
        \Log::info("Images disponibles: " . json_encode(array_keys($images)));
        \Log::info("Données brutes - Nom: '$nomPrenoms', DateLieu: '$dateLieuNaissance', Matricule: '$matricule'");

        // Ignorer les lignes vides
        if (empty($nomPrenoms)) {
            \Log::info("Ligne $row ignorée: nom/prénom vide");
            return null;
        }

        if (in_array($nomPrenoms, ['', '-', '—', '–'])) {
            \Log::info("Ligne $row ignorée: nom/prénom invalide ('$nomPrenoms')");
            return null;
        }

        /**
         * NOM / PRENOM
         */
        $parts  = preg_split('/\s+/', trim($nomPrenoms));
        $nom    = array_shift($parts);
        $prenom = implode(' ', $parts);

        /**
         * DATE / LIEU
         * Après getCellValueFormatted, la date est déjà au format "JJ/MM/AAAA"
         * suivi éventuellement du lieu : "22/11/2007 COTONOU"
         */
        $dateNaissance = null;
        $lieuNaissance = null;

        if (!empty($dateLieuNaissance)) {
            // Format: "JJ/MM/AAAA LIEU" ou "JJ/MM/AAAA"
            if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4})\s*(.*)$/', $dateLieuNaissance, $matches)) {
                $dateNaissance = $this->convertExcelDate($matches[1]);
                $lieuNaissance = !empty($matches[2]) ? strtoupper(trim($matches[2])) : null;
                \Log::info("Date parsée: {$matches[1]} -> " . (is_string($dateNaissance) ? $dateNaissance : 'null'));
            } else {
                // Pas de date trouvée, tout est lieu
                $lieuNaissance = strtoupper(trim($dateLieuNaissance));
                \Log::info("Pas de date trouvée, lieu: '$lieuNaissance'");
            }
        }

        /**
         * SEXE
         */
        $sexeNormalise = 'M';
        if (strtoupper($sexe) === 'F' || str_contains(strtoupper($sexe), 'FEM')) {
            $sexeNormalise = 'F';
        }

        $studentData = new StudentData(
            photo:         $photo,
            matricule:     $matricule,
            nom:           $nom,
            prenom:        $prenom,
            sexe:          $sexeNormalise,
            dateNaissance: $dateNaissance,
            lieuNaissance: $lieuNaissance,
            etablissement: $etablissement,
            eps:           $eps,
            observation:   $observation,
        );

        \Log::info("Étudiant extrait: " . trim("$nom $prenom") . " (photo: " . ($photo ? 'OUI' : 'NON') . ")");

        return $studentData;
    }
}