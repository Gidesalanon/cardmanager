<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Directeur;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EcoleController extends Controller
{
    public function create()
    {
        if (Ecole::where('user_id', Auth::id())->exists()) {
            abort(403, "Une école est déjà associée à ce compte.");
        }
        $schoolYear = SchoolYear::where('is_active', true)->first();
        return view('school.ecole.create', compact('schoolYear'));
    }

    public function store(Request $request)
    {
        if (Ecole::where('user_id', Auth::id())->exists()) {
            return redirect()->back()->with('error', "Une école est déjà associée à ce compte.");
        }

        $request->validate([
            'ecole.nom'                 => 'required|string|max:255|unique:ecoles,nom_ecole',
            'ecole.numero_autorisation' => 'required|string|unique:ecoles,numero_autorisation',
            'ecole.telephone'           => 'nullable|string|max:20',
            'ecole.adresse'             => 'nullable|string|max:255',

            'directeur.nom'             => 'required|string|max:255',
            'directeur.prenom'          => 'required|string|max:255',
            'directeur.sexe'            => 'nullable|in:M,F',
            'directeur.telephone'       => 'nullable|string|max:20',
            'directeur.email'           => 'nullable|email',

            // Signature et cachet maintenant optionnels
            'directeur.signature'       => 'nullable|image|mimes:png,jpg,jpeg',
            'directeur.cachet'          => 'nullable|image|mimes:png,jpg,jpeg',
        ], [
            'ecole.nom.required'                 => "Le nom de l'école est obligatoire.",
            'ecole.nom.unique'                   => "Une école avec ce nom existe déjà.",
            'ecole.numero_autorisation.required' => "Le numéro d'autorisation est obligatoire.",
            'ecole.numero_autorisation.unique'   => "Ce numéro d'autorisation est déjà utilisé.",
            'directeur.nom.required'             => "Le nom du directeur est obligatoire.",
            'directeur.prenom.required'          => "Le prénom du directeur est obligatoire.",
        ]);

        DB::transaction(function () use ($request) {

            $ecole = Ecole::create([
                'user_id'             => Auth::id(),
                'nom_ecole'           => $request->ecole['nom'],
                'numero_autorisation' => $request->ecole['numero_autorisation'],
                'telephone'           => $request->ecole['telephone'] ?? null,
                'adresse_ecole'       => $request->ecole['adresse'] ?? null,
            ]);

            $signaturePath = null;
            $cachetPath    = null;

            if ($request->hasFile('directeur.signature')) {
                $signaturePath = $request->file('directeur.signature')
                    ->store('directeurs/signatures', 'public');
            }

            if ($request->hasFile('directeur.cachet')) {
                $cachetPath = $request->file('directeur.cachet')
                    ->store('directeurs/cachets', 'public');
            }

            Directeur::create([
                'ecole_id'  => $ecole->id,
                'nom'       => $request->directeur['nom'],
                'prenom'    => $request->directeur['prenom'],
                'sexe'      => $request->directeur['sexe'] ?? null,
                'telephone' => $request->directeur['telephone'] ?? null,
                'email'     => $request->directeur['email'] ?? null,
                'signature' => $signaturePath,
                'cachet'    => $cachetPath,
            ]);
        });

        return redirect()
            ->route('school.ecole.show')
            ->with('success', 'Votre école et son directeur créés avec succès.');
    }

    public function show()
    {
        $ecole = Ecole::where('user_id', Auth::id())
            ->with('directeur')
            ->firstOrFail();
        return view('school.ecole.show', compact('ecole'));
    }
}