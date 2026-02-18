<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth; // Importez Auth

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        // 1. Si l'email est déjà vérifié
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        // 2. On marque l'email comme vérifié
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // 3. FORCE LA DÉCONNEXION (pour qu'il se reconnecte)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4. Redirection vers login avec le message de succès
        return redirect()->route('login')->with('status', 'Votre compte a été vérifié avec succès, veuillez vous connecter.');
    }
}