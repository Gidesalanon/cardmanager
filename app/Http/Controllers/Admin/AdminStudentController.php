<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $allClasses = Classe::orderBy('nom')->get();
        $ecoles = Ecole::orderBy('nom_ecole')->get();

        // Supprimer doublons visuels (6ème 6ème 6ème)
        $classes = $allClasses
            ->groupBy(function ($classe) {
                if (preg_match('/(2nde|1ère|Tle)/i', $classe->nom)) {
                    return $classe->nom;
                }
                return preg_replace('/\s+.*/', '', $classe->nom);
            })
            ->map(fn($group) => $group->first())
            ->values();

        // Récupérer les filtres
        $filters = [
            'ecole_id' => $request->get('ecole_id'),
            'classe_id' => $request->get('classe_id'),
            'nom' => $request->get('nom'),
            'sexe' => $request->get('sexe'),
            'division' => $request->get('division'),
        ];

        // Admin : voit tous les élèves de toutes les écoles
        $query = Eleve::with(['ecole', 'classe.serie'])->orderBy('nom');

        // Appliquer les filtres
        $this->appliquerFiltres($query, $filters);

        $perPage = in_array((int) $request->get('per_page'), [15, 30, 50], true)
            ? (int) $request->get('per_page')
            : 15;

        $eleves = $query->paginate($perPage)->withQueryString();

        // Divisions existantes (de l'école choisie, sinon de toutes) pour le filtre
        $divisions = Eleve::query()
            ->when(!empty($filters['ecole_id']), fn ($q) => $q->where('ecole_id', $filters['ecole_id']))
            ->whereNotNull('division')
            ->distinct()
            ->orderBy('division')
            ->pluck('division');

        // Pour l'export des cartes : classes réellement présentes dans chaque école
        $ecoleClasses = $this->classesParEcole();

        // Données pour la modification en masse (fenêtre de la page)
        $bulk = [
            'classes' => $classes->map(fn ($c) => ['id' => $c->id, 'nom' => $c->nom])->values(),
            'series'  => Serie::orderBy('nom')->pluck('nom')->values(),
            'filtres' => [
                'f_classe_id' => $filters['classe_id'] ?? '',
                'f_nom'       => $filters['nom'] ?? '',
                'f_sexe'      => $filters['sexe'] ?? '',
                'f_division'  => $filters['division'] ?? '',
            ],
        ];

        return view('admin.eleves.index', compact(
            'eleves',
            'classes',
            'ecoles',
            'filters',
            'ecoleClasses',
            'divisions',
            'perPage',
            'bulk'
        ));
    }

    /** Même regroupement que la liste des classes de la page : 2nde/1ère/Tle par nom, sinon premier mot. */
    private function niveauCle(?string $nom): string
    {
        $nom = (string) $nom;

        return preg_match('/(2nde|1ère|Tle)/i', $nom) ? $nom : preg_replace('/\s+.*/', '', $nom);
    }

    /** Identifiants de toutes les lignes de classe d'un même niveau (toutes séries). */
    private function classeIdsDuNiveau($classeId): array
    {
        $classe = Classe::find($classeId);

        if (!$classe) {
            return [(int) $classeId];
        }

        $cle = $this->niveauCle($classe->nom);

        return Classe::get(['id', 'nom'])
            ->filter(fn ($c) => $this->niveauCle($c->nom) === $cle)
            ->pluck('id')
            ->all();
    }

    /** Filtres de la liste des élèves (partagés par la liste et la modification en masse). */
    private function appliquerFiltres($query, array $filters): void
    {
        // Appliquer les filtres
        if (!empty($filters['ecole_id'])) {
            $query->where('ecole_id', $filters['ecole_id']);
        }

        if (!empty($filters['classe_id'])) {
            // Toutes les lignes de classe du même niveau (toutes séries), pas seulement la première
            $query->whereIn('classe_id', $this->classeIdsDuNiveau($filters['classe_id']));
        }

        if (!empty($filters['nom'])) {
            // Chaque mot doit se retrouver dans le nom, le prénom ou le matricule
            // (permet de taper « AMOU Zabulon » ou un matricule)
            $termes = preg_split('/\s+/', trim($filters['nom']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($termes as $terme) {
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $terme) . '%';

                $query->where(function ($q) use ($like) {
                    $q->where('nom', 'LIKE', $like)
                      ->orWhere('prenom', 'LIKE', $like)
                      ->orWhere('matricule_edumaster', 'LIKE', $like);
                });
            }
        }

        if (!empty($filters['sexe'])) {
            $query->where('sexe', $filters['sexe']);
        }

        if (!empty($filters['division'])) {
            if ($filters['division'] === '__none__') {
                $query->whereNull('division');
            } elseif (($division = Eleve::normaliserDivision($filters['division'])) !== null) {
                $query->where('division', $division);
            }
        }
    }

    /**
     * Élèves visés par une action en lot (suppression) : mêmes sélections que la modification en lot,
     * toujours limitées à l'école choisie. Retourne [élèves, total attendu ou null].
     */
    private function selectionEnLot(Request $request): array
    {
        $request->validate([
            'ecole_id'       => 'required|exists:ecoles,id',
            'mode'           => 'required|in:ids,filter',
            'ids'            => 'required_if:mode,ids|array|max:1000',
            'ids.*'          => 'integer',
            'expected_total' => 'nullable|integer|min:0',
            'f_classe_id'    => 'nullable|integer',
            'f_nom'          => 'nullable|string|max:100',
            'f_sexe'         => 'nullable|in:M,F',
            'f_division'     => 'nullable|string|max:12',
        ]);

        $ecoleId = (int) $request->ecole_id;
        $query   = Eleve::query()->where('ecole_id', $ecoleId);
        $attendu = null;

        if ($request->mode === 'ids') {
            $ids     = array_values(array_unique(array_map('intval', $request->ids)));
            $attendu = count($ids);
            $query->whereIn('id', $ids);
        } else {
            $this->appliquerFiltres($query, [
                'ecole_id'  => $ecoleId,
                'classe_id' => $request->f_classe_id,
                'nom'       => $request->f_nom,
                'sexe'      => $request->f_sexe,
                'division'  => $request->f_division,
            ]);

            if ($request->filled('expected_total')) {
                $attendu = (int) $request->expected_total;
            }
        }

        $eleves = $query->with(['classe.serie', 'ecole:id,nom_ecole'])
            ->orderBy('classe_id')->orderBy('division')->orderBy('nom')->orderBy('prenom')
            ->get();

        return [$eleves, $attendu];
    }

    /** Message d'erreur si la sélection n'est pas utilisable pour une suppression, sinon null. */
    private function erreurSelectionEnLot($eleves, ?int $attendu): ?string
    {
        $total = $eleves->count();

        if ($total === 0) {
            return 'Aucun élève concerné.';
        }
        if ($total > 1000) {
            return 'Trop d\'élèves en une fois (maximum 1000). Affinez le filtre ou la sélection.';
        }
        if ($attendu !== null && $attendu !== $total) {
            return 'La sélection ne correspond plus aux élèves de cette école. Rechargez la page et recommencez.';
        }

        return null;
    }

    private function empreinteSelection($eleves): string
    {
        return sha1($eleves->pluck('id')->sort()->implode(','));
    }

    /**
     * Suppression en lot, étape 1 : aperçu (rien n'est supprimé).
     * Donne le nombre d'élèves, leur répartition par classe et division, et une empreinte de la sélection
     * que la suppression devra représenter pour agir sur exactement les mêmes élèves.
     */
    public function bulkDeletePreview(Request $request)
    {
        [$eleves, $attendu] = $this->selectionEnLot($request);

        if ($erreur = $this->erreurSelectionEnLot($eleves, $attendu)) {
            return response()->json(['ok' => false, 'message' => $erreur], 422);
        }

        $repartition = $eleves
            ->groupBy(fn ($e) => ($e->classe ? $this->classeLabel($e->classe) : 'Classe inconnue') . '|' . ($e->division ?? ''))
            ->map(function ($groupe, $cle) {
                [$classe, $division] = explode('|', $cle, 2);
                return ['classe' => $classe, 'division' => $division !== '' ? $division : null, 'n' => $groupe->count()];
            })
            ->values();

        return response()->json([
            'ok'          => true,
            'total'       => $eleves->count(),
            'ecole'       => $eleves->first()->ecole->nom_ecole ?? '',
            'repartition' => $repartition,
            'exemples'    => $eleves->take(5)->map(fn ($e) => trim($e->nom . ' ' . $e->prenom))->values(),
            'empreinte'   => $this->empreinteSelection($eleves),
        ]);
    }

    /**
     * Suppression en lot, étape 2 (facultative) : télécharger la liste des élèves visés, au format Excel
     * réimportable (mêmes en-têtes que l'import), pour pouvoir les remettre si c'était une erreur.
     */
    public function bulkDeleteExport(Request $request)
    {
        [$eleves, $attendu] = $this->selectionEnLot($request);

        if ($erreur = $this->erreurSelectionEnLot($eleves, $attendu)) {
            return back()->with('error', $erreur);
        }

        $feuille = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws      = $feuille->getActiveSheet();
        $ws->setTitle('Eleves');

        $entetes = ['N°', 'Matricule', 'Nom et Prénoms', 'Sexe', 'Date de naissance', 'Lieu de naissance', 'Classe', 'Série', 'Division', 'N° Table', 'Téléphone'];
        foreach ($entetes as $i => $titre) {
            $ws->setCellValue([$i + 1, 1], $titre);
        }

        $texte = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
        foreach ($eleves->values() as $r => $e) {
            $ligne = $r + 2;
            $ws->setCellValue([1, $ligne], $r + 1);
            $ws->setCellValueExplicit([2, $ligne], (string) $e->matricule_edumaster, $texte);
            $ws->setCellValueExplicit([3, $ligne], trim($e->nom . ' ' . $e->prenom), $texte);
            $ws->setCellValueExplicit([4, $ligne], (string) $e->sexe, $texte);
            $ws->setCellValueExplicit([5, $ligne], $e->date_naissance ? $e->date_naissance->format('Y-m-d') : '', $texte);
            $ws->setCellValueExplicit([6, $ligne], (string) $e->lieu_naissance, $texte);
            $ws->setCellValueExplicit([7, $ligne], $e->classe ? $e->classe->nom : '', $texte);
            $ws->setCellValueExplicit([8, $ligne], ($e->classe && $e->classe->serie) ? $e->classe->serie->nom : '', $texte);
            $ws->setCellValueExplicit([9, $ligne], (string) $e->division, $texte);
            $ws->setCellValueExplicit([10, $ligne], (string) $e->numero_table, $texte);
            $ws->setCellValueExplicit([11, $ligne], (string) $e->telephone_tuteur, $texte);
        }

        $nom = 'eleves_' . Str::slug($eleves->first()->ecole->nom_ecole ?? 'ecole') . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($feuille) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($feuille))->save('php://output');
        }, $nom, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /**
     * Suppression en lot, étape 3 : suppression définitive des élèves de la sélection.
     * Garde-fous : confirmation écrite (SUPPRIMER), même empreinte que l'aperçu, école choisie uniquement,
     * 1000 élèves au maximum, une seule opération (tout ou rien), journal complet. L'école n'est jamais touchée.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'empreinte'    => 'required|string|size:40',
            'confirmation' => 'required|string|max:50',
        ]);

        if (trim($request->confirmation) !== 'SUPPRIMER') {
            return back()->with('error', 'Confirmation incorrecte : tapez exactement SUPPRIMER pour valider. Aucun élève n\'a été supprimé.');
        }

        [$eleves, $attendu] = $this->selectionEnLot($request);

        if ($erreur = $this->erreurSelectionEnLot($eleves, $attendu)) {
            return back()->with('error', $erreur . ' Aucun élève n\'a été supprimé.');
        }

        if (! hash_equals($this->empreinteSelection($eleves), (string) $request->empreinte)) {
            return back()->with('error', 'La sélection a changé depuis l\'aperçu. Aucun élève n\'a été supprimé : recommencez.');
        }

        $total   = $eleves->count();
        $ecole   = $eleves->first()->ecole->nom_ecole ?? '';
        $ecoleId = (int) $request->ecole_id;
        $ids     = $eleves->pluck('id')->all();
        $fichiers = $eleves->pluck('photo')->merge($eleves->pluck('qr_code'))->filter()->unique()->values();

        // Liste complète conservée dans le journal : id|matricule|nom|prénoms|classe|division|date de naissance
        $liste = $eleves->map(fn ($e) => implode('|', [
            $e->id, $e->matricule_edumaster, $e->nom, $e->prenom,
            $e->classe ? $this->classeLabel($e->classe) : '', $e->division ?? '',
            $e->date_naissance ? $e->date_naissance->format('Y-m-d') : '',
        ]))->implode(';');

        DB::transaction(function () use ($ids) {
            Eleve::whereIn('id', $ids)->delete();
        });

        // Fichiers (photo, QR) supprimés seulement s'ils ne servent plus à aucun autre élève
        $encoreUtilises = Eleve::whereIn('photo', $fichiers)->pluck('photo')
            ->merge(Eleve::whereIn('qr_code', $fichiers)->pluck('qr_code'))->unique();
        foreach ($fichiers->diff($encoreUtilises) as $fichier) {
            try {
                Storage::disk('public')->delete($fichier);
            } catch (\Throwable $t) {
                Log::warning('Suppression en masse : fichier non supprimé ' . $fichier . ' : ' . $t->getMessage());
            }
        }

        Log::info('Suppression en masse des élèves', [
            'admin_id' => auth()->id(),
            'ecole_id' => $ecoleId,
            'ecole'    => $ecole,
            'mode'     => $request->mode,
            'nombre'   => $total,
            'eleves'   => $liste, // format : id|matricule|nom|prénoms|classe|division|naissance
        ]);

        return back()->with('success', $total . ' élève' . ($total > 1 ? 's' : '') . ' supprimé' . ($total > 1 ? 's' : '') . ' définitivement de l\'école ' . $ecole . '.');
    }

    /**
     * Modification en masse (division et/ou classe) des élèves d'UNE école.
     * Mode "ids" : élèves cochés ; mode "filter" : tous les élèves du filtre courant.
     * Seuls la division, la classe et la série sont modifiables ici.
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ecole_id'        => 'required|exists:ecoles,id',
            'mode'            => 'required|in:ids,filter',
            'ids'             => 'required_if:mode,ids|array|max:1000',
            'ids.*'           => 'integer',
            'expected_total'  => 'nullable|integer|min:0',
            'division_action' => 'required|in:keep,set,clear',
            'new_division'    => 'nullable|string|max:12',
            'new_classe_id'   => 'nullable|exists:classes,id',
            'new_serie'       => 'nullable|string|exists:series,nom',
            'f_classe_id'     => 'nullable|integer',
            'f_nom'           => 'nullable|string|max:100',
            'f_sexe'          => 'nullable|in:M,F',
            'f_division'      => 'nullable|string|max:12',
        ]);

        // --- Ce qui doit changer ---
        $changes = [];

        if ($request->division_action === 'set') {
            $division = Eleve::normaliserDivision($request->new_division);

            if ($division === null) {
                return back()->with('error', 'Indiquez la division à appliquer.');
            }

            $changes['division'] = $division;
        } elseif ($request->division_action === 'clear') {
            $changes['division'] = null;
        }

        if ($request->filled('new_classe_id')) {
            $changes['classe_id'] = Classe::resolveId($request->new_classe_id, $request->new_serie);
        }

        if (empty($changes)) {
            return back()->with('error', 'Aucune modification demandée.');
        }

        // --- Quels élèves (toujours limités à l'école choisie) ---
        $ecoleId = (int) $request->ecole_id;
        $query   = Eleve::query()->where('ecole_id', $ecoleId);
        $attendu = null;

        if ($request->mode === 'ids') {
            $ids     = array_values(array_unique(array_map('intval', $request->ids)));
            $attendu = count($ids);
            $query->whereIn('id', $ids);
        } else {
            $this->appliquerFiltres($query, [
                'ecole_id'  => $ecoleId,
                'classe_id' => $request->f_classe_id,
                'nom'       => $request->f_nom,
                'sexe'      => $request->f_sexe,
                'division'  => $request->f_division,
            ]);

            if ($request->filled('expected_total')) {
                $attendu = (int) $request->expected_total;
            }
        }

        $eleves = $query->get(['id', 'classe_id', 'division']);
        $total  = $eleves->count();

        if ($total === 0) {
            return back()->with('error', 'Aucun élève concerné par cette modification.');
        }

        if ($total > 1000) {
            return back()->with('error', 'Trop d\'élèves en une fois (maximum 1000). Affinez le filtre.');
        }

        if ($attendu !== null && $attendu !== $total) {
            return back()->with('error', 'La sélection ne correspond plus aux élèves de cette école. Rechargez la page et recommencez.');
        }

        // --- Application : tout ou rien, avec trace des anciennes valeurs ---
        $avant = $eleves
            ->map(fn ($e) => $e->id . ':' . $e->classe_id . ':' . ($e->division ?? ''))
            ->implode(',');

        DB::transaction(function () use ($eleves, $changes) {
            Eleve::whereIn('id', $eleves->pluck('id')->all())
                ->update($changes + ['updated_at' => now()]);
        });

        Log::info('Modification en masse des élèves', [
            'admin_id' => auth()->id(),
            'ecole_id' => $ecoleId,
            'mode'     => $request->mode,
            'nombre'   => $total,
            'changes'  => $changes,
            'avant'    => $avant, // format : id:classe_id:division
        ]);

        $resume = [];

        if (array_key_exists('division', $changes)) {
            $resume[] = 'division : ' . ($changes['division'] ?? 'aucune');
        }

        if (isset($changes['classe_id'])) {
            $nouvelle = Classe::with('serie')->find($changes['classe_id']);
            $resume[] = 'classe : ' . ($nouvelle ? $this->classeLabel($nouvelle) : $changes['classe_id']);
        }

        return back()->with('success', $total . ' élève' . ($total > 1 ? 's' : '') . ' mis à jour (' . implode(', ', $resume) . ').');
    }

    public function create()
    {
        // Admin : voit toutes les écoles
        $ecoles = Ecole::orderBy('nom_ecole')->get();

        // Un niveau = une seule ligne (6e, 5e... existent en base avec 1 ligne par série) :
        // sinon le menu afficherait 9 fois "6e". Même règle que la liste, la modification et le menu côté école.
        $classes = Classe::orderBy('nom')->get()
            ->groupBy(function ($classe) {
                if (preg_match('/(2nde|1ère|Tle)/i', $classe->nom)) {
                    return $classe->nom;
                }
                return preg_replace('/\s+.*/', '', $classe->nom);
            })
            ->map(fn ($group) => $group->first())
            ->values();

        return view('admin.eleves.create', compact('ecoles', 'classes'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'ecole_id'            => 'required|exists:ecoles,id',
        'classe_id'           => 'required|exists:classes,id',
        'serie'               => 'nullable|string|exists:series,nom',
        'division'            => 'nullable|string|max:12',
        'nom'                 => 'required|string|max:255',
        'prenom'              => 'required|string|max:255',
        'sexe'                => 'required|in:M,F',
        'date_naissance'      => 'required|date',
        'lieu_naissance'      => 'required|string|max:255',
        'telephone_tuteur'    => 'required|string|max:20',
        'photo'               => 'required|image|max:2048',
        'matricule_edumaster' => 'nullable|string|unique:eleves,matricule_edumaster',
    ]);

    $photoPath = $request->file('photo')->store('eleves/photos', 'public');

    // QR Code
    $qrContent  = 'Nom: ' . $validated['nom'] . "\nPrenom: " . $validated['prenom'] . "\nEducMaster: " . ($validated['matricule_edumaster'] ?? '');
    $qrSlug     = Str::slug($validated['matricule_edumaster'] ?? $validated['nom'] . '_' . time());
    $qrCodePath = 'eleves/qrcodes/' . $qrSlug . '.png';
    $qrFullPath = storage_path('app/public/' . $qrCodePath);

    if (!file_exists(dirname($qrFullPath))) {
        mkdir(dirname($qrFullPath), 0755, true);
    }

    $writer = new PngWriter();
    $writer->write(new QrCode($qrContent))->saveToFile($qrFullPath);

    Eleve::create([
        'ecole_id'            => $validated['ecole_id'],
        'classe_id'           => Classe::resolveId($validated['classe_id'], $validated['serie'] ?? null),
        'division'            => $validated['division'] ?? null,
        'nom'                 => $validated['nom'],
        'prenom'              => $validated['prenom'],
        'sexe'                => $validated['sexe'],
        'date_naissance'      => $validated['date_naissance'],
        'lieu_naissance'      => $validated['lieu_naissance'],
        'telephone_tuteur'    => $validated['telephone_tuteur'],
        'photo'               => $photoPath,
        'matricule_edumaster' => $validated['matricule_edumaster'] ?? null,
        'qr_code'             => $qrCodePath,
    ]);

    return redirect()
        ->route('admin.students.index')
        ->with('success', 'Élève enregistré avec succès.');
}

public function update(Request $request, Eleve $eleve)
{
    $validated = $request->validate([
        'ecole_id'            => 'required|exists:ecoles,id',
        'classe_id'           => 'required|exists:classes,id',
        'serie'               => 'nullable|string|exists:series,nom',
        'division'            => 'nullable|string|max:12',
        'nom'                 => 'required|string|max:255',
        'prenom'              => 'required|string|max:255',
        'sexe'                => 'required|in:M,F',
        'date_naissance'      => 'nullable|date',
        'lieu_naissance'      => 'nullable|string|max:255',
        'telephone_tuteur'    => 'required|string|max:20',
        'photo'               => 'nullable|image|max:2048',
        'matricule_edumaster' => 'nullable|string|unique:eleves,matricule_edumaster,' . $eleve->id,
    ]);

    if ($request->hasFile('photo')) {
        if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
            Storage::disk('public')->delete($eleve->photo);
        }
        $validated['photo'] = $request->file('photo')->store('eleves/photos', 'public');
    }

    $validated['classe_id'] = Classe::resolveId($validated['classe_id'], $validated['serie'] ?? null);
    unset($validated['serie']);

    $eleve->update($validated);

    return redirect()
        ->route('admin.students.index')
        ->with('success', 'Élève modifié avec succès.');
}

    public function edit(Eleve $eleve)
    {
        // Admin : voit toutes les écoles
        $ecoles = Ecole::orderBy('nom_ecole')->get();

        $eleve->load('classe.serie');

        // Un niveau = une seule ligne (6e, 5e... existent en base avec 1 ligne par série) :
        // sinon le menu afficherait 9 fois "6e". Même règle que le menu côté école.
        $classes = Classe::orderBy('nom')->get()
            ->groupBy(function ($classe) {
                if (preg_match('/(2nde|1ère|Tle)/i', $classe->nom)) {
                    return $classe->nom;
                }
                return preg_replace('/\s+.*/', '', $classe->nom);
            })
            ->map(fn ($group) => $group->first())
            ->values();

        return view('admin.eleves.edit', compact('eleve', 'ecoles', 'classes'));
    }

    public function destroy(Eleve $eleve)
    {
        if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
            Storage::disk('public')->delete($eleve->photo);
        }

        if ($eleve->qr_code && Storage::disk('public')->exists($eleve->qr_code)) {
            Storage::disk('public')->delete($eleve->qr_code);
        }

        $eleve->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Élève supprimé avec succès.');
    }


    public function exportCardImage(Eleve $eleve)
    {
        $eleve->load(['ecole', 'classe']);
        $activeYear = \App\Models\SchoolYear::active()->first();

        // Binaire wkhtmltoimage : chemin configurable (WKHTML_IMG_BINARY, voir config/snappy.php),
        // ancien chemin Windows conservé en secours pour le poste local.
        $wkhtmltoimage = collect([
            config('snappy.image.binary'),
            'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltoimage.exe',
        ])->first(fn ($chemin) => $chemin && file_exists($chemin));

        if (!$wkhtmltoimage) {
            return back()->with('error', "L'export en image n'est pas disponible sur ce serveur (wkhtmltoimage introuvable). Utilisez l'export PDF.");
        }

        $html = view('admin.eleves.card.card', compact('eleve', 'activeYear'))->render();

        // Fichiers temporaires dans un dossier NON public (jamais accessibles par /storage), nom unique
        $dossier = storage_path('app/temp');
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $base      = $dossier . DIRECTORY_SEPARATOR . 'carte_' . uniqid();
        $htmlPath  = $base . '.html';
        $imagePath = $base . '.png';

        file_put_contents($htmlPath, $html);

        $command = escapeshellarg($wkhtmltoimage)
            . ' --enable-local-file-access --width 1012 --height 638 '
            . escapeshellarg($htmlPath) . ' ' . escapeshellarg($imagePath);

        exec($command . ' 2>&1', $output, $resultCode);
        @unlink($htmlPath);

        if ($resultCode !== 0 || !file_exists($imagePath)) {
            @unlink($imagePath);

            Log::error('Export image carte : échec de wkhtmltoimage', [
                'code'   => $resultCode,
                'sortie' => implode(' | ', array_slice($output, 0, 5)),
            ]);

            return back()->with('error', "La génération de l'image a échoué. Utilisez l'export PDF.");
        }

        return response()
            ->download($imagePath, 'carte_' . Str::slug((string) $eleve->matricule_edumaster) . '.png')
            ->deleteFileAfterSend(true);
    }


    public function exportCardPdf(Eleve $eleve)
{
    $eleve->load(['ecole.directeur', 'classe.serie']);
    $activeYear = \App\Models\SchoolYear::active()->first();

    $pdf = Pdf::loadView('admin.eleves.card.cards', compact('eleve', 'activeYear'))
        ->setPaper([0, 0, 242.64, 153.07], 'portrait');

    return $pdf->download('carte_' . $eleve->matricule_edumaster . '.pdf');
}

public function exportEcoleCardsPdf(Request $request)
{
    $ecoleId = $request->get('ecole_id');

    if (!$ecoleId) {
        return redirect()
            ->route('admin.students.index')
            ->with('error', 'Veuillez sélectionner une école.');
    }

    $ecole = Ecole::with('directeur')->findOrFail($ecoleId);
    $activeYear = \App\Models\SchoolYear::active()->first();

    $eleves = Eleve::with(['ecole.directeur', 'classe.serie'])
        ->where('ecole_id', $ecoleId)
        ->orderBy('nom')
        ->get();

    if ($eleves->isEmpty()) {
        return redirect()
            ->route('admin.students.index')
            ->with('error', 'Aucun élève trouvé pour cette école.');
    }

    $pdf = Pdf::loadView('admin.eleves.card.bulk-cards', compact('eleves', 'activeYear'))
        ->setPaper([0, 0, 242.64, 153.07], 'portrait');

    $filename = 'cartes_' . Str::slug($ecole->nom_ecole) . '.pdf';

    return $pdf->download($filename);
}

    /*
    |--------------------------------------------------------------------------
    | CARTES PAR ÉCOLE ET PAR CLASSE
    |--------------------------------------------------------------------------
    */

    /**
     * Libellé d'une classe : "4e", "CM2", "2nde C"...
     * La série n'est ajoutée que pour 2nde / 1ère / Tle (comme sur la carte).
     */
    private function classeLabel(Classe $classe): string
    {
        $label = $classe->nom;

        if (preg_match('/^(2nde|1ère|Tle|Terminale)$/i', trim($classe->nom)) && $classe->serie) {
            $label .= ' ' . $classe->serie->nom;
        }

        return $label;
    }

    /**
     * Identifiants de toutes les lignes "classes" qui forment une même classe
     * à l'affichage : 6e..3e = toutes séries confondues, 2nde/1ère/Tle = une série.
     */
    private function classeGroupIds(Classe $classe): array
    {
        $query = Classe::where('nom', $classe->nom)
            ->where('section_id', $classe->section_id);

        if (preg_match('/^(2nde|1ère|Tle|Terminale)$/i', trim($classe->nom))) {
            $query->where('serie_id', $classe->serie_id);
        }

        return $query->pluck('id')->all();
    }

    /**
     * Nom de fichier : ecole_golo_djigbe_classe_4eme.pdf
     * ou, avec une division : ecole_golo_djigbe_classe_5eme_m1.pdf
     * ou, élèves sans division d'un niveau qui en a : ecole_golo_djigbe_classe_5eme_sans_division.pdf
     */
    private function cardsFileName(Ecole $ecole, Classe $classe, ?string $division = null, bool $sansDivision = false): string
    {
        $suffixe    = $division ?? ($sansDivision ? 'sans division' : '');
        $classeSlug = Str::slug(trim($this->classeLabel($classe) . ' ' . $suffixe), '_');

        // 6e, 5e, 4e, 3e => 6eme, 5eme, 4eme, 3eme
        $classeSlug = preg_replace('/^(\d)e(?=_|$)/', '$1eme', $classeSlug);

        return 'ecole_' . Str::slug($ecole->nom_ecole, '_') . '_classe_' . $classeSlug . '.pdf';
    }

    /**
     * Pour chaque école : les classes qui ont des élèves, regroupées par niveau.
     * Format : [ecole_id => [['label' => '5e', 'total' => 135, 'options' => [...]], ...]]
     *
     * Un niveau sans division n'a qu'une option : "36|" (tous ses élèves).
     * Un niveau avec divisions en a plusieurs :
     *   "36|*"  toute la classe (toutes divisions confondues)
     *   "36|M2" une division
     *   "36|!"  les élèves sans division (seulement s'il y en a)
     */
    private function classesParEcole()
    {
        // Tolérant : la page Élèves reste utilisable même si la migration "division" n'est pas encore passée
        $colonnes = Schema::hasColumn('eleves', 'division')
            ? ['ecole_id', 'classe_id', 'division']
            : ['ecole_id', 'classe_id'];

        $counts = DB::table('eleves')
            ->select(array_merge($colonnes, [DB::raw('COUNT(*) as total')]))
            ->groupBy($colonnes)
            ->get();

        $classesById = Classe::with('serie')
            ->whereIn('id', $counts->pluck('classe_id')->unique())
            ->get()
            ->keyBy('id');

        return $counts
            ->groupBy('ecole_id')
            ->map(function ($rows) use ($classesById) {
                return $rows
                    ->filter(fn ($r) => $classesById->has($r->classe_id))
                    ->groupBy(fn ($r) => $this->classeLabel($classesById[$r->classe_id]))
                    ->map(function ($niveau, $label) {
                        $id    = $niveau->first()->classe_id;
                        $total = $niveau->sum('total');

                        $divisions = $niveau
                            ->filter(fn ($r) => !empty($r->division ?? null))
                            ->groupBy(fn ($r) => $r->division)
                            ->map(fn ($g) => $g->sum('total'))
                            ->sortKeys(SORT_NATURAL);

                        // Niveau sans division : une seule entrée, comme avant
                        if ($divisions->isEmpty()) {
                            return [
                                'label'   => $label,
                                'total'   => $total,
                                'options' => [['value' => $id . '|', 'label' => $label, 'total' => $total]],
                            ];
                        }

                        $options = [[
                            'value'     => $id . '|*',
                            'label'     => $label . ' — toute la classe',
                            'total'     => $total,
                            'aggregate' => true,
                        ]];

                        foreach ($divisions as $division => $effectif) {
                            $options[] = [
                                'value' => $id . '|' . $division,
                                'label' => $label . ' ' . $division,
                                'total' => $effectif,
                            ];
                        }

                        $sans = $total - $divisions->sum();

                        if ($sans > 0) {
                            $options[] = ['value' => $id . '|!', 'label' => $label . ' sans division', 'total' => $sans];
                        }

                        return ['label' => $label, 'total' => $total, 'options' => $options];
                    })
                    ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();
            });
    }

    private function buildCardsPdf($eleves)
    {
        $activeYear = \App\Models\SchoolYear::active()->first();

        return Pdf::loadView('admin.eleves.card.bulk-cards', compact('eleves', 'activeYear'))
            ->setPaper([0, 0, 242.64, 153.07], 'portrait');
    }

    /**
     * Cartes d'UNE classe (et division) d'UNE école (classe_id = "<id>|<division>"),
     * ou de toutes les classes de l'école dans un ZIP (classe_id = "all"), un PDF par classe.
     */
    public function exportClassCardsPdf(Request $request)
    {
        $request->validate([
            'ecole_id'  => 'required|exists:ecoles,id',
            'classe_id' => 'required',
        ]);

        $ecole = Ecole::with('directeur')->findOrFail($request->ecole_id);

        if ($request->classe_id === 'all') {
            return $this->exportAllClassesZip($ecole);
        }

        // Valeur reçue : "<id de classe>|<reste>" avec reste = "*" (toute la classe), "!" (sans division),
        // "<division>", ou vide (élèves sans division d'un niveau qui n'a pas de divisions)
        [$classeId, $reste] = array_pad(explode('|', (string) $request->classe_id, 2), 2, '');
        $toute    = $reste === '*';
        $sans     = $reste === '!';
        $division = ($toute || $sans) ? null : Eleve::normaliserDivision($reste);

        validator(['classe_id' => $classeId], [
            'classe_id' => 'required|integer|exists:classes,id',
        ])->validate();

        $classe = Classe::with('serie')->findOrFail($classeId);

        $eleves = Eleve::with(['ecole.directeur', 'classe.serie'])
            ->where('ecole_id', $ecole->id)
            ->whereIn('classe_id', $this->classeGroupIds($classe))
            ->when(
                !$toute,
                fn ($q) => $q->when(
                    $division === null,
                    fn ($q2) => $q2->whereNull('division'),
                    fn ($q2) => $q2->where('division', $division)
                )
            )
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        if ($eleves->isEmpty()) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Aucun élève en ' . trim($this->classeLabel($classe) . ' ' . ($division ?? ($sans ? 'sans division' : ''))) . ' pour cette école.');
        }

        return $this->buildCardsPdf($eleves)
            ->download($this->cardsFileName($ecole, $classe, $division, $sans));
    }

    private function exportAllClassesZip(Ecole $ecole)
    {
        if (!class_exists(\ZipArchive::class)) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', "L'extension PHP ZipArchive n'est pas disponible sur ce serveur.");
        }

        $groupes = Eleve::with(['ecole.directeur', 'classe.serie'])
            ->where('ecole_id', $ecole->id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get()
            ->filter(fn ($e) => $e->classe)
            ->groupBy(fn ($e) => $this->classeLabel($e->classe) . '|' . ($e->division ?? ''));

        if ($groupes->isEmpty()) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Aucun élève trouvé pour cette école.');
        }

        // Niveaux (libellés) qui ont au moins une division : leurs élèves sans division
        // sont nommés "..._sans_division.pdf" pour ne pas passer pour la classe entière
        $niveauxAvecDivision = $groupes->keys()
            ->filter(fn ($k) => !str_ends_with($k, '|'))
            ->map(fn ($k) => explode('|', $k, 2)[0])
            ->unique()
            ->values()
            ->all();

        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $tmpDir = storage_path('app/temp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipPath = $tmpDir . '/cartes_' . $ecole->id . '_' . uniqid() . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', "Impossible de créer l'archive ZIP.");
        }

        $tmpFiles = [];
        $classeEnCours = null;

        try {
            foreach ($groupes as $label => $eleves) {
                $classeEnCours = trim(str_replace('|', ' ', $label));

                $tmp = $tmpDir . '/pdf_' . uniqid() . '.pdf';
                $tmpFiles[] = $tmp;

                $pdf = $this->buildCardsPdf($eleves);
                file_put_contents($tmp, $pdf->output());
                unset($pdf);
                gc_collect_cycles();

                $premier = $eleves->first();
                $sans    = $premier->division === null
                    && in_array($this->classeLabel($premier->classe), $niveauxAvecDivision, true);

                $zip->addFile($tmp, $this->cardsFileName($ecole, $premier->classe, $premier->division, $sans));
            }

            // ZipArchive lit les fichiers au moment de close() : ne rien supprimer avant
            $zip->close();
        } catch (\Throwable $e) {
            Log::error('Export ZIP cartes : ' . $e->getMessage(), [
                'ecole_id' => $ecole->id,
                'classe'   => $classeEnCours,
                'fichier'  => $e->getFile() . ':' . $e->getLine(),
            ]);

            try { $zip->close(); } catch (\Throwable $ignored) {}
            foreach ($tmpFiles as $f) { @unlink($f); }
            @unlink($zipPath);

            return redirect()
                ->route('admin.students.index')
                ->with('error', 'La génération du ZIP a échoué (classe : ' . ($classeEnCours ?? '?') . '). Génère les classes une par une ; le détail est dans le journal.');
        }

        foreach ($tmpFiles as $f) {
            @unlink($f);
        }

        $zipName = 'ecole_' . Str::slug($ecole->nom_ecole, '_') . '_cartes_par_classe.zip';

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
