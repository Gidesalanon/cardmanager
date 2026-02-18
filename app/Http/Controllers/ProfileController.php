<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
            public function update(Request $request): RedirectResponse
{
    $user = $request->user();

    // Validation
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        'password' => ['nullable', 'confirmed', 'min:8'],
    ]);

    // On ne change PAS le nom et l'email (même s'ils sont envoyés, on garde les anciens)
    // Mais on doit laisser la validation passer pour que le reste s'enregistre.
    
    // Gestion de l'image (Avatar)
    if ($request->hasFile('avatar')) {
        // Supprimer l'ancien avatar s'il existe physiquement
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Stockage du nouvel avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
    }

    // Gestion du mot de passe
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    // On retourne avec le status pour afficher le message vert
    return back()->with('status', 'profile-updated');
}

    /**
     * Delete the user's account.
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
}
