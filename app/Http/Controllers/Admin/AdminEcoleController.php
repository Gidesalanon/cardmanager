<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Directeur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminEcoleController extends Controller
{
    public function index()
    {
        $ecoles = Ecole::with(['directeur', 'user'])->orderBy('nom_ecole')->get();
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
            'directeur.sexe'            => 'required|in:M,F',
            'directeur.telephone'       => 'nullable|string|max:20',
            'directeur.email'           => 'nullable|email',
            'directeur.signature'       => 'nullable|image|mimes:png,jpg,jpeg',
            'directeur.cachet'          => 'nullable|image|mimes:png,jpg,jpeg',
        ], [
            'ecole.nom.required'                 => "Le nom de l'école est obligatoire.",
            'ecole.nom.unique'                   => "Une école avec ce nom existe déjà.",
            'ecole.numero_autorisation.required' => "Le numéro d'autorisation est obligatoire.",
            'ecole.numero_autorisation.unique'   => "Ce numéro d'autorisation est déjà utilisé.",
            'directeur.nom.required'             => "Le nom du directeur est obligatoire.",
            'directeur.prenom.required'          => "Le prénom du directeur est obligatoire.",
            'directeur.sexe.required'            => "Le sexe du directeur est obligatoire.",
            'directeur.sexe.in'                  => "Le sexe du directeur doit être Masculin ou Féminin.",
        ]);

        DB::transaction(function () use ($request) {

            $ecole = Ecole::create([
                'user_id'             => null,
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
            ->route('admin.ecoles.index')
            ->with('success', 'École créée avec succès.');
    }

    public function edit(Ecole $ecole)
    {
        $ecole->load('directeur');

        // Comptes école qu'on peut rattacher : ceux qui n'ont pas encore d'école, plus le compte actuel
        $comptes = User::where('role', 'ecole')
            ->where(function ($q) use ($ecole) {
                $q->whereDoesntHave('ecole')->orWhere('id', $ecole->user_id);
            })
            ->orderBy('name')
            ->get();

        return view('admin.ecoles.edit', compact('ecole', 'comptes'));
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
            'directeur.sexe'            => 'required|in:M,F',
            'directeur.telephone'       => 'nullable|string|max:20',
            'directeur.email'           => 'nullable|email',
            'directeur.signature'       => 'nullable|image|mimes:png,jpg,jpeg',
            'directeur.cachet'          => 'nullable|image|mimes:png,jpg,jpeg',

            'compte_user_id'            => [
                'nullable', 'integer',
                Rule::exists('users', 'id')->where('role', 'ecole'),
                'unique:ecoles,user_id,' . $ecole->id,
            ],
        ], [
            'directeur.sexe.required'  => "Le sexe du directeur est obligatoire.",
            'directeur.sexe.in'        => "Le sexe du directeur doit être Masculin ou Féminin.",
            'compte_user_id.exists'    => "Ce compte n'est pas un compte école valide.",
            'compte_user_id.unique'    => "Ce compte est déjà rattaché à une autre école.",
        ]);

        DB::transaction(function () use ($request, $ecole) {

            $ecole->update([
                'nom_ecole'           => $request->ecole['nom'],
                'numero_autorisation' => $request->ecole['numero_autorisation'],
                'telephone'           => $request->ecole['telephone'] ?? null,
                'adresse_ecole'       => $request->ecole['adresse'] ?? null,
            ]);

            // Rattachement / détachement du compte école (uniquement si le champ est envoyé)
            if ($request->has('compte_user_id')) {
                $ecole->user_id = $request->input('compte_user_id') ?: null;
                $ecole->save();
            }

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