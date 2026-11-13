<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Eleve;
use App\Models\Classe;
use App\Models\Ecole;
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

        return view('admin.eleves.index', compact(
            'eleves',
            'classes',
            'ecoles',
            'filters',
            'ecoleClasses',
            'divisions',
            'perPage'
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

    public function create()
    {
        // Admin : voit toutes les écoles
        $ecoles = Ecole::orderBy('nom_ecole')->get();
        $classes = Classe::orderBy('nom')->get();

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
        $classes = Classe::orderBy('nom')->get();

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

        $html = view('admin.eleves.card.card', compact('eleve', 'activeYear'))->render();

        $htmlPath = storage_path('app/public/temp_card.html');
        $imagePath = storage_path('app/public/carte_' . $eleve->matricule_edumaster . '.png');

        file_put_contents($htmlPath, $html);

        // ADAPTE ce chemin si besoin
        $wkhtmltoimage = '"C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe"';

        $command = $wkhtmltoimage
            . " --enable-local-file-access"
            . " --width 1012"
            . " --height 638"
            . " \"$htmlPath\""
            . " \"$imagePath\"";

        exec($command, $output, $resultCode);

        // DEBUG si problème
        if ($resultCode !== 0 || !file_exists($imagePath)) {
            dd($output, $resultCode);
        }

        return response()->download($imagePath)->deleteFileAfterSend(true);
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
     */
    private function cardsFileName(Ecole $ecole, Classe $classe, ?string $division = null): string
    {
        $classeSlug = Str::slug(trim($this->classeLabel($classe) . ' ' . ($division ?? '')), '_');

        // 6e, 5e, 4e, 3e => 6eme, 5eme, 4eme, 3eme
        $classeSlug = preg_replace('/^(\d)e(?=_|$)/', '$1eme', $classeSlug);

        return 'ecole_' . Str::slug($ecole->nom_ecole, '_') . '_classe_' . $classeSlug . '.pdf';
    }

    /**
     * Pour chaque école : la liste des classes (et divisions) qui ont des élèves, avec l'effectif.
     * Format : [ecole_id => [['value' => '36|M1', 'label' => '5e M1', 'total' => 32], ...]]
     * value = "<id de classe>|<division>" ; division vide = élèves sans division.
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
                    ->groupBy(fn ($r) => $this->classeLabel($classesById[$r->classe_id]) . '|' . ($r->division ?? ''))
                    ->map(function ($group) use ($classesById) {
                        $first    = $group->first();
                        $division = $first->division ?? null;

                        return [
                            'value' => $first->classe_id . '|' . ($division ?? ''),
                            'label' => $this->classeLabel($classesById[$first->classe_id]) . ($division ? ' ' . $division : ''),
                            'total' => $group->sum('total'),
                        ];
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

        // Valeur reçue : "<id de classe>|<division>" (division vide = élèves sans division)
        [$classeId, $division] = array_pad(explode('|', (string) $request->classe_id, 2), 2, '');
        $division = Eleve::normaliserDivision($division);

        validator(['classe_id' => $classeId], [
            'classe_id' => 'required|integer|exists:classes,id',
        ])->validate();

        $classe = Classe::with('serie')->findOrFail($classeId);

        $eleves = Eleve::with(['ecole.directeur', 'classe.serie'])
            ->where('ecole_id', $ecole->id)
            ->whereIn('classe_id', $this->classeGroupIds($classe))
            ->when(
                $division === null,
                fn ($q) => $q->whereNull('division'),
                fn ($q) => $q->where('division', $division)
            )
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        if ($eleves->isEmpty()) {
            return redirect()
                ->route('admin.students.index')
                ->with('error', 'Aucun élève en ' . trim($this->classeLabel($classe) . ' ' . ($division ?? '')) . ' pour cette école.');
        }

        return $this->buildCardsPdf($eleves)
            ->download($this->cardsFileName($ecole, $classe, $division));
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

                $zip->addFile($tmp, $this->cardsFileName($ecole, $eleves->first()->classe, $eleves->first()->division));
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
