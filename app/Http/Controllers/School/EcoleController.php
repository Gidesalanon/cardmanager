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
    /**
     * Formulaire de création
     */
    public function create()
    {
        // 🔐 Blocage : une seule école par compte
        if (Ecole::where('user_id', Auth::id())->exists()) {
            abort(403, "Une école est déjà associée à ce compte.");
        }
        $schoolYear = SchoolYear::where('is_active', true)->first();

        return view('school.ecole.create', compact('schoolYear'));
    }

    /**
     * Enregistrement école + directeur
     */
    public function store(Request $request)
    {
        // 🔐 Sécurité back-end
        if (Ecole::where('user_id', Auth::id())->exists()) {
            abort(403, "Une école est déjà associée à ce compte.");
        }

        $request->validate([
            'ecole.nom' => 'required|string|max:255',
            'ecole.numero_autorisation' => 'required|string|unique:ecoles,numero_autorisation',
            'ecole.telephone' => 'nullable|string|max:20',
            'ecole.adresse' => 'nullable|string|max:255',

            'directeur.nom' => 'required|string|max:255',
            'directeur.prenom' => 'required|string|max:255',
            'directeur.sexe' => 'nullable|in:M,F',
            'directeur.telephone' => 'nullable|string|max:20',
            'directeur.email' => 'nullable|email',

            'directeur.signature' => 'nullable|image|mimes:png,jpg,jpeg',
            'directeur.cachet' => 'nullable|image|mimes:png,jpg,jpeg',
        ]);

        DB::transaction(function () use ($request) {

            /** ==========================
             * 1️⃣ Création de l’école
             * ========================== */
            $ecole = Ecole::create([
                'user_id' => Auth::id(),
                'nom_ecole' => $request->ecole['nom'],
                'numero_autorisation' => $request->ecole['numero_autorisation'],
                'telephone' => $request->ecole['telephone'] ?? null,
                'adresse_ecole' => $request->ecole['adresse'] ?? null,
            ]);

            /** ==========================
             * 2️⃣ Upload signature & cachet
             * ========================== */
            $signaturePath = null;
            $cachetPath = null;

            if ($request->hasFile('directeur.signature')) {
                $signaturePath = $request->file('directeur.signature')
                    ->store('directeurs/signatures', 'public');
            }

            if ($request->hasFile('directeur.cachet')) {
                $cachetPath = $request->file('directeur.cachet')
                    ->store('directeurs/cachets', 'public');
            }

            /** ==========================
             * 3️⃣ Création du directeur
             * ========================== */
            Directeur::create([
                'ecole_id' => $ecole->id,
                'nom' => $request->directeur['nom'],
                'prenom' => $request->directeur['prenom'],
                'sexe' => $request->directeur['sexe'] ?? null,
                'telephone' => $request->directeur['telephone'] ?? null,
                'email' => $request->directeur['email'] ?? null,
                'signature' => $signaturePath,
                'cachet' => $cachetPath,
            ]);
        });

        return redirect()
            ->route('school.ecole.show')
            ->with('success', 'Votre école et son directeur créés avec succès.');
    }

    /**
     * Affichage Mon école
     */
    public function show()
    {
        $ecole = Ecole::where('user_id', Auth::id())
            ->with('directeur')
            ->firstOrFail();

        return view('school.ecole.show', compact('ecole'));
    }
}
