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
        ];

        // Admin : voit tous les élèves de toutes les écoles
        $query = Eleve::with(['ecole', 'classe'])->orderBy('nom');

        // Appliquer les filtres
        if (!empty($filters['ecole_id'])) {
            $query->where('ecole_id', $filters['ecole_id']);
        }

        if (!empty($filters['classe_id'])) {
            $query->where('classe_id', $filters['classe_id']);
        }

        if (!empty($filters['nom'])) {
            $query->where('name', 'LIKE', '%' . $filters['nom'] . '%');
        }

        if (!empty($filters['sexe'])) {
            $query->where('sexe', $filters['sexe']);
        }

        $eleves = $query->paginate(15)->withQueryString();

        return view('admin.eleves.index', compact(
            'eleves',
            'classes',
            'ecoles',
            'filters'
        ));
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
        'classe_id'           => $validated['classe_id'],
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
}
