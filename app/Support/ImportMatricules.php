<?php

namespace App\Support;

use App\Models\Ecole;
use App\Models\Eleve;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Import d'élèves : que faire des élèves qui existent déjà ?
 *
 * - Matricule déjà enregistré : on n'importe que les nouveaux élèves et on explique chaque ligne ignorée
 *   (déjà dans cette école / dans une autre école / matricule d'un autre élève).
 * - Élève déjà présent dans CETTE école (mêmes nom, prénoms et date de naissance), avec ou sans matricule :
 *   ignoré, pour qu'un fichier réimporté (même sans matricule) ne crée jamais de doublons.
 * - Même élève présent plusieurs fois dans le fichier : importé une seule fois.
 * Rien n'est jamais écrasé ni déplacé.
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
        $parMatricule = empty($matricules)
            ? collect()
            : Eleve::whereIn('matricule_edumaster', $matricules)
                ->with(['ecole:id,nom_ecole', 'classe:id,nom'])
                ->get()
                ->keyBy('matricule_edumaster');

        // Élèves déjà dans l'école ayant la même date de naissance qu'une ligne du fichier
        $dates = collect($students)->map(fn ($s) => self::dateIso($s['date_naissance'] ?? null))->filter()->unique()->values()->all();
        $parIdentite = empty($dates)
            ? collect()
            : Eleve::where('ecole_id', $ecole->id)
                ->whereIn('date_naissance', $dates)
                ->with('classe:id,nom')
                ->get()
                ->groupBy(fn ($e) => self::cle($e->nom . ' ' . $e->prenom, $e->date_naissance?->format('Y-m-d')));

        if ($parMatricule->isEmpty() && $parIdentite->isEmpty()) {
            // Seul cas restant : le même élève deux fois dans le fichier (traité plus bas)
            if (! self::aDesDoublonsDansLeFichier($students)) {
                return [$students, [], ''];
            }
        }

        $aImporter = [];
        $ignores   = [];
        $vus       = [];

        foreach ($students as $i => $s) {
            $matricule = $s['matricule'] ?? null;
            $nomComplet = trim(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? ''));
            $existant  = $matricule ? $parMatricule->get($matricule) : null;

            // 1) Matricule déjà enregistré
            if ($existant) {
                if ((int) $existant->ecole_id !== (int) $ecole->id) {
                    $raison = 'autre_ecole';
                } elseif (self::memePersonne($s, $existant)) {
                    $raison = 'deja';
                } else {
                    $raison = 'autre_eleve';
                }
                $ignores[] = self::ligne($matricule, $nomComplet, $raison, $existant);
                continue;
            }

            // 2) Même élève déjà dans cette école (identité), ou déjà vu plus haut dans le fichier
            $cle = self::cle($nomComplet, self::dateIso($s['date_naissance'] ?? null));
            if ($cle !== null) {
                if (isset($vus[$cle])) {
                    $ignores[] = self::ligne($matricule ?? '', $nomComplet, 'doublon_fichier', null);
                    continue;
                }
                $deja = $parIdentite->get($cle)?->first();
                if ($deja) {
                    if (empty($deja->matricule_edumaster)) {
                        $raison = 'deja_sans_matricule';
                    } elseif (empty($matricule) || $deja->matricule_edumaster === $matricule) {
                        $raison = 'deja';
                    } else {
                        $raison = 'autre_matricule';
                    }
                    $ignores[] = self::ligne($matricule ?? '', $nomComplet, $raison, $deja);
                    continue;
                }
                $vus[$cle] = true;
            }

            $aImporter[$i] = $s;   // on garde l'index d'origine : les messages « ligne N » restent justes
        }

        if (empty($ignores)) {
            return [$students, [], ''];
        }

        Log::info('Import : élèves ignorés (déjà enregistrés)', [
            'ecole_id' => $ecole->id,
            'importes' => count($aImporter),
            'ignores'  => $ignores,
        ]);

        return [$aImporter, $ignores, self::resume($ignores)];
    }

    /** Même personne ? Mêmes mots dans nom + prénoms (ordre, casse et accents sans importance) et même date de naissance si connue. */
    public static function memePersonne(array $s, Eleve $e): bool
    {
        if (self::mots(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? '')) !== self::mots($e->nom . ' ' . $e->prenom)) {
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

    private static function mots(string $texte): array
    {
        $m = preg_split('/[\s\-\']+/u', Str::lower(Str::ascii(trim($texte))), -1, PREG_SPLIT_NO_EMPTY);
        sort($m);
        return $m;
    }

    /** Clé d'identité : mots du nom + prénoms (triés) et date. Null si on ne peut pas identifier l'élève. */
    private static function cle(string $nomComplet, ?string $dateIso): ?string
    {
        $mots = self::mots($nomComplet);
        if (empty($mots) || empty($dateIso)) {
            return null;
        }
        return implode(' ', $mots) . '|' . $dateIso;
    }

    private static function dateIso($date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable $t) {
            return null;
        }
    }

    private static function aDesDoublonsDansLeFichier(array $students): bool
    {
        $cles = [];
        foreach ($students as $s) {
            $c = self::cle(trim(($s['nom'] ?? '') . ' ' . ($s['prenom'] ?? '')), self::dateIso($s['date_naissance'] ?? null));
            if ($c !== null && isset($cles[$c])) {
                return true;
            }
            if ($c !== null) {
                $cles[$c] = true;
            }
        }
        return false;
    }

    private static function ligne($matricule, string $nom, string $raison, ?Eleve $existant): array
    {
        return [
            'matricule'          => (string) $matricule,
            'nom'                => $nom,
            'raison'             => $raison,
            'ecole'              => $existant?->ecole?->nom_ecole ?? '',
            'classe'             => $existant?->classe?->nom ?? '',
            'existant'           => $existant ? trim($existant->nom . ' ' . $existant->prenom) : '',
            'existant_matricule' => $existant?->matricule_edumaster ?? '',
        ];
    }

    public static function resume(array $ignores): string
    {
        if (empty($ignores)) {
            return '';
        }

        $groupes = [
            'deja'                => 'déjà enregistré(s) dans cette école (ignoré(s), rien n\'est modifié)',
            'deja_sans_matricule' => 'déjà enregistré(s) dans cette école, sans matricule (même nom, prénoms et date de naissance ; ignoré(s), le matricule du fichier n\'est pas appliqué)',
            'autre_matricule'     => 'même élève déjà enregistré sous un AUTRE matricule (non importé(s), à vérifier)',
            'autre_ecole'         => 'déjà enregistré(s) dans une AUTRE école (non importé(s))',
            'autre_eleve'         => 'matricule déjà attribué à un AUTRE élève de cette école (non importé(s), vérifier une faute de frappe)',
            'doublon_fichier'     => 'présent(s) plusieurs fois dans le fichier (importé(s) une seule fois)',
        ];

        $lignes = [count($ignores) . ' ligne(s) ignorée(s) car l\'élève existe déjà :'];

        foreach ($groupes as $cle => $libelle) {
            $items = array_values(array_filter($ignores, fn ($x) => $x['raison'] === $cle));
            if (empty($items)) {
                continue;
            }

            $liste = array_map(function ($x) use ($cle) {
                $detail = match ($cle) {
                    'autre_ecole'     => $x['ecole'] . ', ' . $x['classe'],
                    'autre_eleve'     => 'déjà attribué à ' . $x['existant'] . ', ' . $x['classe'],
                    'autre_matricule' => 'déjà sous le matricule ' . $x['existant_matricule'] . ', ' . $x['classe'],
                    'doublon_fichier' => 'ligne répétée',
                    default           => 'déjà en ' . $x['classe'],
                };
                return trim($x['matricule'] . ' ' . $x['nom']) . ' (' . $detail . ')';
            }, array_slice($items, 0, self::MAX_PAR_GROUPE));

            $reste = count($items) - self::MAX_PAR_GROUPE;
            $lignes[] = '• ' . count($items) . ' ' . $libelle . ' : ' . implode(' ; ', $liste)
                . ($reste > 0 ? " … et $reste autre(s)" : '') . '.';
        }

        return implode("\n", $lignes);
    }
}
