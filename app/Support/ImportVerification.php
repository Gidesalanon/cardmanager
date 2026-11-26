<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Import d'élèves : vérification de TOUTES les lignes avant d'écrire quoi que ce soit.
 *
 * Avant : la première ligne incomplète arrêtait l'import avec une erreur technique (500) et un numéro de ligne
 * ambigu, et une valeur trop longue laissait échapper l'erreur SQL brute à l'écran.
 * Maintenant : l'admin voit d'un coup toutes les lignes à corriger, avec le nom de chaque élève et la raison.
 * Rien n'est enregistré tant que le fichier a un problème (comportement « tout ou rien » conservé).
 */
class ImportVerification
{
    private const MAX_AFFICHES = 10;

    /**
     * Champ => [libellé, longueur maximale]. Les limites sont larges pour de vraies données mais restent sous les
     * 255 caractères de la base : un nom très long servirait aussi à fabriquer un nom de fichier QR impossible à écrire.
     */
    private const CHAMPS_TEXTE = [
        'nom'              => ['nom', 100],
        'prenom'           => ['prénoms', 150],
        'lieu_naissance'   => ['lieu de naissance', 100],
        'matricule'        => ['matricule', 50],
        'numero_table'     => ['numéro de table', 50],
        'telephone_tuteur' => ['téléphone', 30],
        'nationalite'      => ['nationalité', 50],
    ];

    /** @return array<int, array{numero:int, nom:string, raisons:array<int,string>}> */
    public static function lignesAcorriger(array $students): array
    {
        $problemes = [];

        foreach ($students as $i => $s) {
            $raisons = [];

            if (trim((string) ($s['nom'] ?? '')) === '') {
                $raisons[] = 'nom manquant';
            }
            if (trim((string) ($s['prenom'] ?? '')) === '') {
                $raisons[] = 'prénoms manquants';
            }

            $date = $s['date_naissance'] ?? null;
            if (empty($date)) {
                $raisons[] = 'date de naissance manquante';
            } elseif (! self::dateValide($date)) {
                $raisons[] = 'date de naissance illisible (« ' . mb_substr((string) $date, 0, 30) . ' »)';
            }

            foreach (self::CHAMPS_TEXTE as $cle => [$libelle, $max]) {
                $longueur = mb_strlen((string) ($s[$cle] ?? ''));
                if ($longueur > $max) {
                    $raisons[] = "$libelle trop long ($longueur caractères, maximum $max)";
                }
            }

            if (! empty($raisons)) {
                $nom = trim(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? ''));
                $problemes[] = [
                    'numero'  => $i + 1,
                    'nom'     => $nom !== '' ? mb_substr($nom, 0, 60) : '(sans nom)',
                    'raisons' => $raisons,
                ];
            }
        }

        return $problemes;
    }

    public static function message(array $problemes): string
    {
        $lignes = [
            count($problemes) . " élève(s) à corriger dans le fichier. Rien n'a été enregistré :",
        ];

        foreach (array_slice($problemes, 0, self::MAX_AFFICHES) as $p) {
            $lignes[] = '• Élève n°' . $p['numero'] . ' « ' . $p['nom'] . ' » : ' . implode(', ', $p['raisons']) . '.';
        }

        $reste = count($problemes) - self::MAX_AFFICHES;
        if ($reste > 0) {
            $lignes[] = "… et $reste autre(s).";
        }

        $lignes[] = "Corrigez ces lignes dans le fichier puis relancez l'import (le numéro est l'ordre de l'élève dans la liste).";

        return implode("\n", $lignes);
    }

    private static function dateValide($date): bool
    {
        try {
            Carbon::parse($date);
            return true;
        } catch (\Throwable $t) {
            return false;
        }
    }
}
