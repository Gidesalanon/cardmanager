<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash; // Importation manquante corrigée
use Illuminate\Support\Facades\Storage; // Importation manquante corrigée

class ProfileController extends Controller
{
    /**
     * Affiche le formulaire de profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Met à jour les informations du profil.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Messages personnalisés pour que le Toast affiche exactement votre texte
        $customMessages = [
            'current_password.required' => 'Confirmer votre mot de passe',
            'current_password.current_password' => 'Mot de passe incorrect',
        ];

        // Validation unique et propre
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'name' => ['required', 'string', 'max:255'],
            // L'email n'est pas dans la validation car il est en readonly dans la vue
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ], $customMessages);

        // 1. Sauvegarde du Nom
        $user->name = $request->name;

        // 2. Sauvegarde de l'Avatar (Photo de profil)
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->avatar) { 
                Storage::disk('public')->delete($user->avatar); 
            }
            // Enregistrer la nouvelle photo
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        // 3. Sauvegarde du nouveau mot de passe (si rempli)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Rafraîchir la session pour que le nom change immédiatement dans le Topnav
        $request->session()->put('user_name', $user->name);

        return back()->with('status', 'profile-updated');
    }

    /**
     * Supprime le compte de l'utilisateur.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Supprime uniquement l'avatar (facultatif si vous avez la route).
     */
    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }
        return back()->with('status', 'profile-updated');
    }
}