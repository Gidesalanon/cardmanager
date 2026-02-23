<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $action = $request->input('action');

        $customMessages = [
            'current_password.required' => 'Confirmer votre mot de passe',
            'current_password.current_password' => 'Mot de passe incorrect',
        ];

        if ($action === 'profile') {
            // === ACTION : ENREGISTRER LE PROFIL ===
            
            // Validation pour le profil (pas besoin de mot de passe)
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            ]);

            // Nom
            $user->name = $request->name;

            // Avatar
            if ($request->hasFile('avatar')) {
                if ($user->avatar) { Storage::disk('public')->delete($user->avatar); }
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            $user->save();

            return back()->with('status', 'profile-updated');

        } elseif ($action === 'password') {
            // === ACTION : CHANGER LE MOT DE PASSE ===
            
            // Validation pour le mot de passe
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', 'min:8'],
            ], $customMessages);

            // Mot de passe
            $user->password = Hash::make($request->password);
            $user->save();

            return back()->with('status', 'password-updated');

        }

        // Action par défaut (si aucun action spécifié)
        return back()->with('error', 'Action non reconnue');
    }
}