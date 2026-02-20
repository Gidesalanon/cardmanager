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

    // On valide d'abord le mot de passe actuel pour autoriser la suite
    $request->validate([
        'current_password' => ['required', 'current_password'],
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        'password' => ['nullable', 'confirmed', 'min:8'],
    ]);

    // Mise à jour du nom et email
    $user->name = $request->name;
    $user->email = $request->email;

    // Gestion de la photo
    if ($request->hasFile('avatar')) {
        if ($user->avatar) { Storage::disk('public')->delete($user->avatar); }
        $user->avatar = $request->file('avatar')->store('avatars', 'public');
    }

    // Changement de mot de passe (si rempli)
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

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
