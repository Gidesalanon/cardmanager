<?php

namespace App\Http\Controllers\School;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eleve;
use App\Models\Classe;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\SchoolYear; 
class StudentController extends Controller
{
    public function index(Request $request)
    {
        $selectedClasse = $request->classe_id ?? null;
        $activeYear = \App\Models\SchoolYear::where('is_active',1)->first();

        $query = Eleve::query();

        if ($selectedClasse) {
            $query->where('classe_id', $selectedClasse);
        }

        $eleves = $query->orderBy('nom')->get();

        $allClasses = Classe::orderBy('nom')->get();

        $classes = $allClasses
            ->groupBy(function ($classe) {
                if (preg_match('/(2nde|1ère|Tle)/i', $classe->nom)) {
                    return $classe->nom;
                }
                return preg_replace('/\s+.*/', '', $classe->nom);
            })
            ->map(fn($group) => $group->first())
            ->values();

        return view('school.eleves.index', compact(
            'eleves',
            'classes',
            'selectedClasse',
            'activeYear'
        ));
    }

    public function create()
{
    $allClasses = \App\Models\Classe::orderBy('nom')->get();

    $classes = $allClasses
        ->groupBy(function ($classe) {

            if (preg_match('/(2nde|1ère|Tle)/i', $classe->nom)) {
                return $classe->nom;
            }

            return preg_replace('/\s+.*/', '', $classe->nom);
        })
        ->map(fn($group) => $group->first())
        ->values();

    return view('school.eleves.create', compact('classes'));
}


    public function store(Request $request)
    { 
       $user = \Illuminate\Support\Facades\Auth::user();
       $ecole = $user->ecole;
        $validated = $request->validate([
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
        $photoPath = $request->file('photo')
            ->store('eleves/photos','public');

        // Génération numero_table automatique
        $numeroTable = 'TB'.str_pad(Eleve::max('id') + 1,5,'0',STR_PAD_LEFT);

        // Génération QR
        $qrCodePath = 'eleves/qrcodes/' . Str::slug($validated['matricule_edumaster']) . '.png';
        $qrFullPath = storage_path('app/public/'.$qrCodePath);

        if (!file_exists(dirname($qrFullPath))) {
            mkdir(dirname($qrFullPath), 0755, true);
        }

        $qrCode = new QrCode($validated['matricule_edumaster']);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $result->saveToFile($qrFullPath);

        Eleve::create([
            'ecole_id' => $ecole->id,
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
            ->route('school.students.index')
            ->with('success','Élève enregistré avec succès.');
    }

    public function edit(Eleve $eleve)
    {
        $classes = Classe::orderBy('nom')->get();
        return view('school.eleves.edit', compact('eleve','classes'));
    }

    public function update(Request $request, Eleve $eleve)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'telephone_tuteur' => 'required|string|max:20',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
                Storage::disk('public')->delete($eleve->photo);
            }

            $validated['photo'] = $request->file('photo')
                ->store('eleves/photos','public');
        }

        $eleve->update($validated);

        return redirect()
            ->route('school.students.index')
            ->with('success','Élève modifié avec succès.');
    }

    public function destroy(Eleve $eleve)
    {
        if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
            Storage::disk('public')->delete($eleve->photo);
        }

        $eleve->delete();

        return redirect()
            ->route('school.students.index')
            ->with('success','Élève supprimé avec succès.');
    }



        
public function downloadCard($id)
{
    // On récupère l'élève avec sa classe et son école
    $eleve = Eleve::with(['classe'])->findOrFail($id);
    
    // On récupère l'année scolaire active
    $activeYear = SchoolYear::where('is_active', 1)->first();

    // On prépare les données pour la vue en les nommant "student" 
    // pour que ton code HTML (que tu as envoyé) fonctionne sans erreur
    $data = [
        'student' => $eleve,
        'activeYear' => $activeYear
    ];

    $pdf = Pdf::loadView('school.eleves.card-pdf', $data);

    // Format ISO Carte (86mm x 55mm)
    $pdf->setPaper([0, 0, 243.78, 155.91], 'portrait');

    return $pdf->stream('Carte_'.$eleve->nom.'.pdf');
}


public function viewCards(Request $request)
{
    $activeYear = SchoolYear::where('is_active', 1)->first();
    
    // On peut filtrer par classe si on veut
    $query = Eleve::with('classe');
    if ($request->classe_id) {
        $query->where('classe_id', $request->classe_id);
    }
    
    $eleves = $query->get();

    return view('school.eleves.cards-view', compact('eleves', 'activeYear'));
}

}
