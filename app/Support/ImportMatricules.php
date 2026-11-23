<?php

namespace App\Support;

use App\Models\Ecole;
use App\Models\Eleve;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Import d'élèves : que faire des matricules déjà enregistrés ?
 *
 * Avant : un seul matricule déjà présent faisait refuser TOUT le fichier, avec seulement la liste des numéros.
 * Maintenant : on n'importe que les nouveaux élèves, et on dit précisément pourquoi chaque autre est ignoré
 * (déjà dans cette école / dans une autre école / matricule d'un autre élève). Rien n'est écrasé ni déplacé.
 */
class ImportMatricules
{
    private const MAX_PAR_GROUPE = 8;

    /**
     * @param  array  $students  élèves du fichier (matricules déjà nettoyés)
     * @return array{0: array, 1: array, 2: string}  [élèves à importer (clés d'origine conservées), ignorés, résumé lisible]
     */
    public static function trier(array $students, Ecole $ecole): array
    {
        $matricules = collect($students)->pluck('matricule')->filter()->unique()->values()->all();
        if (empty($matricules)) {
            return [$students, [], ''];
        }

        $existants = Eleve::whereIn('matricule_edumaster', $matricules)
            ->with(['ecole:id,nom_ecole', 'classe:id,nom'])
            ->get()
            ->keyBy('matricule_edumaster');

        if ($existants->isEmpty()) {
            return [$students, [], ''];
        }

        $aImporter = [];
        $ignores   = [];

        foreach ($students as $i => $s) {
            $matricule = $s['matricule'] ?? null;
            $existant  = $matricule ? $existants->get($matricule) : null;

            if (! $existant) {
                $aImporter[$i] = $s;   // on garde l'index d'origine : les messages « ligne N » restent justes
                continue;
            }

            if ((int) $existant->ecole_id !== (int) $ecole->id) {
                $raison = 'autre_ecole';
            } elseif (self::memePersonne($s, $existant)) {
                $raison = 'deja';
            } else {
                $raison = 'autre_eleve';
            }

            $ignores[] = [
                'matricule' => $matricule,
                'nom'       => trim(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? '')),
                'raison'    => $raison,
                'ecole'     => $existant->ecole->nom_ecole ?? '?',
                'classe'    => $existant->classe->nom ?? '?',
                'existant'  => trim($existant->nom . ' ' . $existant->prenom),
            ];
        }

        Log::info('Import : élèves ignorés (matricule déjà enregistré)', [
            'ecole_id'  => $ecole->id,
            'importes'  => count($aImporter),
            'ignores'   => $ignores,
        ]);

        return [$aImporter, $ignores, self::resume($ignores)];
    }

    /** Même personne ? Mêmes mots dans nom + prénoms (ordre, casse et accents sans importance) et même date de naissance si connue. */
    public static function memePersonne(array $s, Eleve $e): bool
    {
        $mots = function (string $texte): array {
            $m = preg_split('/[\s\-\']+/u', Str::lower(Str::ascii(trim($texte))), -1, PREG_SPLIT_NO_EMPTY);
            sort($m);
            return $m;
        };

        if ($mots(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? '')) !== $mots($e->nom . ' ' . $e->prenom)) {
            return false;
        }

        $date = $s['date_naissance'] ?? null;
        if ($date && $e->date_naissance) {
            try {
                return Carbon::parse($date)->isSameDay($e->date_naissance);
            } catch (\Throwable $t) {
                return true;   // date illisible dans le fichier : on ne conclut pas à une différence
            }
        }

        return true;
    }

    public static function resume(array $ignores): string
    {
        if (empty($ignores)) {
            return '';
        }

        $groupes = [
            'deja'        => 'déjà enregistré(s) dans cette école (ignoré(s), rien n\'est modifié)',
            'autre_ecole' => 'déjà enregistré(s) dans une AUTRE école (non importé(s))',
            'autre_eleve' => 'matricule déjà attribué à un AUTRE élève de cette école (non importé(s), vérifier une faute de frappe)',
        ];

        $lignes = [count($ignores) . ' ligne(s) ignorée(s) car leur matricule existe déjà :'];

        foreach ($groupes as $cle => $libelle) {
            $items = array_values(array_filter($ignores, fn ($x) => $x['raison'] === $cle));
            if (empty($items)) {
                continue;
            }

            $liste = array_map(function ($x) use ($cle) {
                $detail = match ($cle) {
                    'autre_ecole' => $x['ecole'] . ', ' . $x['classe'],
                    'autre_eleve' => 'déjà attribué à ' . $x['existant'] . ', ' . $x['classe'],
                    default       => 'déjà en ' . $x['classe'],
                };
                return $x['matricule'] . ' ' . $x['nom'] . ' (' . $detail . ')';
            }, array_slice($items, 0, self::MAX_PAR_GROUPE));

            $reste = count($items) - self::MAX_PAR_GROUPE;
            $lignes[] = '• ' . count($items) . ' ' . $libelle . ' : ' . implode(' ; ', $liste)
                . ($reste > 0 ? " … et $reste autre(s)" : '') . '.';
        }

        return implode("\n", $lignes);
    }
}
