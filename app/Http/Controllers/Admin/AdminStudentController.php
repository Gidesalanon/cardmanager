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

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $allClasses = Classe::orderBy('nom')->get();

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

        $selectedClasse = $request->classe_id ?? null;

        $query = Eleve::with(['ecole', 'classe'])->orderBy('nom');

        if ($selectedClasse) {
            $query->where('classe_id', $selectedClasse);
        }

        $eleves = $query->get();

        return view('admin.eleves.index', compact(
            'eleves',
            'classes',
            'selectedClasse'
        ));
    }

    public function create()
    {
        $ecoles = Ecole::orderBy('nom_ecole')->get();
        $classes = Classe::orderBy('nom')->get();

        return view('admin.eleves.create', compact('ecoles', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ecole_id' => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date',
            'lieu_naissance' => 'required|string|max:255',
            'telephone_tuteur' => 'required|string|max:20',
            'photo' => 'required|image|max:2048',
            'matricule_edumaster' => 'required|string|unique:eleves,matricule_edumaster',
        ]);

        // Upload photo
        $photoPath = $request->file('photo')->store('eleves/photos', 'public');

        // Numéro de table automatique sécurisé
        $nextId = Eleve::max('id') + 1;
        $numeroTable = 'TB' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        // Génération QR code
        $qrCodePath = 'eleves/qrcodes/' . Str::slug($validated['matricule_edumaster']) . '.png';
        $qrFullPath = storage_path('app/public/' . $qrCodePath);

        if (!file_exists(dirname($qrFullPath))) {
            mkdir(dirname($qrFullPath), 0755, true);
        }

        $qrCode = new QrCode($validated['matricule_edumaster']);
        $writer = new PngWriter();
        $writer->write($qrCode)->saveToFile($qrFullPath);

        Eleve::create([
            'ecole_id' => $validated['ecole_id'],
            'classe_id' => $validated['classe_id'],
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'sexe' => $validated['sexe'],
            'date_naissance' => $validated['date_naissance'],
            'lieu_naissance' => $validated['lieu_naissance'],
            'telephone_tuteur' => $validated['telephone_tuteur'],
            'photo' => $photoPath,
            'matricule_edumaster' => $validated['matricule_edumaster'],
            'numero_table' => $numeroTable,
            'qr_code' => $qrCodePath,
        ]);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Élève enregistré avec succès.');
    }

    public function edit(Eleve $eleve)
    {
        $ecoles = Ecole::orderBy('nom_ecole')->get();
        $classes = Classe::orderBy('nom')->get();

        return view('admin.eleves.edit', compact('eleve', 'ecoles', 'classes'));
    }

    public function update(Request $request, Eleve $eleve)
    {
        \Log::info("=== DÉBUT UPDATE ÉLÈVE ===");
        \Log::info("Élève ID: " . $eleve->id);
        \Log::info("Données reçues: " . json_encode($request->all()));
        
        $validated = $request->validate([
            'ecole_id' => 'required|exists:ecoles,id',
            'classe_id' => 'required|exists:classes,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'telephone_tuteur' => 'required|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);

        \Log::info("Validation réussie !");
        \Log::info("Données validées: " . json_encode($validated));

        if ($request->hasFile('photo')) {
            \Log::info("Photo détectée, traitement...");
            if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
                Storage::disk('public')->delete($eleve->photo);
                \Log::info("Ancienne photo supprimée: " . $eleve->photo);
            }

            $validated['photo'] = $request->file('photo')
                ->store('eleves/photos', 'public');
            \Log::info("Nouvelle photo enregistrée: " . $validated['photo']);
        }

        \Log::info("Mise à jour de l'élève...");
        $result = $eleve->update($validated);
        \Log::info("Résultat update: " . ($result ? 'SUCCESS' : 'FAILED'));

        \Log::info("Redirection vers admin.students.index");
        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Élève modifié avec succès.');
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
        $eleve->load(['ecole', 'classe']);
        $activeYear = \App\Models\SchoolYear::active()->first();

        $pdf = Pdf::loadView('admin.eleves.card.cards', compact('eleve', 'activeYear'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('carte_' . $eleve->matricule_edumaster . '.pdf');
    }

    public function exportClassCardsPdf()
    {
        $activeYear = \App\Models\SchoolYear::active()->first();
        $eleves = Eleve::with(['ecole', 'classe'])->get();

        if ($eleves->isEmpty()) {
            abort(404, 'Aucun élève trouvé.');
        }

        $pdf = Pdf::loadView('admin.eleves.card.class-cards', compact('eleves', 'activeYear'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('cartes_classe.pdf');
    }
}
