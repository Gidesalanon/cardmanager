<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Directeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminEcoleController extends Controller
{
    public function index()
    {
        $ecoles = Ecole::with('directeur')->orderBy('nom_ecole')->get();
        return view('admin.ecoles.index', compact('ecoles'));
    }

    public function create()
    {
        return view('admin.ecoles.create');
    }

    public function store(Request $request)
    {
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
            'directeur.signature'       => 'required|image|mimes:png,jpg,jpeg',
            'directeur.cachet'          => 'required|image|mimes:png,jpg,jpeg',
        ], [
            'ecole.nom.required'                    => "Le nom de l'école est obligatoire.",
            'ecole.nom.unique'                      => "Une école avec ce nom existe déjà.",
            'ecole.numero_autorisation.required'    => "Le numéro d'autorisation est obligatoire.",
            'ecole.numero_autorisation.unique'      => "Ce numéro d'autorisation est déjà utilisé.",
            'directeur.nom.required'                => "Le nom du directeur est obligatoire.",
            'directeur.prenom.required'             => "Le prénom du directeur est obligatoire.",
            'directeur.email.email'                 => "L'adresse email du directeur n'est pas valide.",
            'directeur.sexe.in'                     => "Le sexe doit être M ou F.",
            'directeur.signature.required'          => "La signature du directeur est obligatoire.",
            'directeur.signature.image'             => "La signature doit être une image.",
            'directeur.signature.mimes'             => "La signature doit être en format PNG, JPG ou JPEG.",
            'directeur.cachet.required'             => "Le cachet est obligatoire.",
            'directeur.cachet.image'                => "Le cachet doit être une image.",
            'directeur.cachet.mimes'                => "Le cachet doit être en format PNG, JPG ou JPEG.",
        ]);

        DB::transaction(function () use ($request) {

            // 1. Création école (user_id null — créée par l'admin)
            $ecole = Ecole::create([
                'user_id'             => null,
                'nom_ecole'           => $request->ecole['nom'],
                'numero_autorisation' => $request->ecole['numero_autorisation'],
                'telephone'           => $request->ecole['telephone'] ?? null,
                'adresse_ecole'       => $request->ecole['adresse'] ?? null,
            ]);

            // 2. Upload signature & cachet
            $signaturePath = $request->file('directeur.signature')
                ->store('directeurs/signatures', 'public');

            $cachetPath = $request->file('directeur.cachet')
                ->store('directeurs/cachets', 'public');

            // 3. Création directeur
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
            ->route('admin.ecoles.index')
            ->with('success', 'École créée avec succès.');
    }

    public function edit(Ecole $ecole)
    {
        $ecole->load('directeur');
        return view('admin.ecoles.edit', compact('ecole'));
    }

    public function update(Request $request, Ecole $ecole)
    {
        $request->validate([
            'ecole.nom'                 => 'required|string|max:255|unique:ecoles,nom_ecole,' . $ecole->id,
            'ecole.numero_autorisation' => 'required|string|unique:ecoles,numero_autorisation,' . $ecole->id,
            'ecole.telephone'           => 'nullable|string|max:20',
            'ecole.adresse'             => 'nullable|string|max:255',

            'directeur.nom'             => 'required|string|max:255',
            'directeur.prenom'          => 'required|string|max:255',
            'directeur.sexe'            => 'nullable|in:M,F',
            'directeur.telephone'       => 'nullable|string|max:20',
            'directeur.email'           => 'nullable|email',
            'directeur.signature'       => 'nullable|image|mimes:png,jpg,jpeg',
            'directeur.cachet'          => 'nullable|image|mimes:png,jpg,jpeg',
        ], [
            'ecole.nom.required'                    => "Le nom de l'école est obligatoire.",
            'ecole.nom.unique'                      => "Une école avec ce nom existe déjà.",
            'ecole.numero_autorisation.required'    => "Le numéro d'autorisation est obligatoire.",
            'ecole.numero_autorisation.unique'      => "Ce numéro d'autorisation est déjà utilisé.",
            'directeur.nom.required'                => "Le nom du directeur est obligatoire.",
            'directeur.prenom.required'             => "Le prénom du directeur est obligatoire.",
            'directeur.email.email'                 => "L'adresse email du directeur n'est pas valide.",
            'directeur.sexe.in'                     => "Le sexe doit être M ou F.",
        ]);

        DB::transaction(function () use ($request, $ecole) {

            // 1. Mise à jour école
            $ecole->update([
                'nom_ecole'           => $request->ecole['nom'],
                'numero_autorisation' => $request->ecole['numero_autorisation'],
                'telephone'           => $request->ecole['telephone'] ?? null,
                'adresse_ecole'       => $request->ecole['adresse'] ?? null,
            ]);

            // 2. Mise à jour directeur
            $directeur     = $ecole->directeur;
            $signaturePath = $directeur->signature;
            $cachetPath    = $directeur->cachet;

            if ($request->hasFile('directeur.signature')) {
                if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
                    Storage::disk('public')->delete($signaturePath);
                }
                $signaturePath = $request->file('directeur.signature')
                    ->store('directeurs/signatures', 'public');
            }

            if ($request->hasFile('directeur.cachet')) {
                if ($cachetPath && Storage::disk('public')->exists($cachetPath)) {
                    Storage::disk('public')->delete($cachetPath);
                }
                $cachetPath = $request->file('directeur.cachet')
                    ->store('directeurs/cachets', 'public');
            }

            $directeur->update([
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
            ->route('admin.ecoles.index')
            ->with('success', 'École modifiée avec succès.');
    }

    public function destroy(Ecole $ecole)
    {
        DB::transaction(function () use ($ecole) {
            $directeur = $ecole->directeur;

            if ($directeur) {
                if ($directeur->signature && Storage::disk('public')->exists($directeur->signature)) {
                    Storage::disk('public')->delete($directeur->signature);
                }
                if ($directeur->cachet && Storage::disk('public')->exists($directeur->cachet)) {
                    Storage::disk('public')->delete($directeur->cachet);
                }
                $directeur->delete();
            }

            // Supprime aussi tous les élèves liés
            foreach ($ecole->eleves as $eleve) {
                if ($eleve->photo && Storage::disk('public')->exists($eleve->photo)) {
                    Storage::disk('public')->delete($eleve->photo);
                }
                if ($eleve->qr_code && Storage::disk('public')->exists($eleve->qr_code)) {
                    Storage::disk('public')->delete($eleve->qr_code);
                }
                $eleve->delete();
            }

            $ecole->delete();
        });

        return redirect()
            ->route('admin.ecoles.index')
            ->with('success', 'École supprimée avec succès.');
    }
}