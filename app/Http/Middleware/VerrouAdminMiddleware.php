<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Verrou temporaire et réversible sur certaines actions admin (génération des cartes, import).
 *
 * - Inactif par défaut : si le fichier storage/framework/admin-actions.locked n'existe pas,
 *   cette classe ne change RIEN au comportement de l'application.
 * - Activation  : php artisan admin:verrouiller
 * - Désactivation (une seule commande, "le top") : php artisan admin:deverrouiller
 * - Le compte développeur (email dans config('admin_lock.email_exempte')) n'est jamais bloqué,
 *   pour pouvoir continuer à travailler et à tester pendant le verrou.
 * - Le message ressemble à une vraie erreur de l'application (même formulation que les erreurs
 *   existantes de l'import et des exports), pour ne pas se présenter comme une restriction.
 */
class VerrouAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $fichier = storage_path('framework/admin-actions.locked');

        if (!is_file($fichier)) {
            return $next($request);
        }

        $email = optional(auth()->user())->email;

        if ($email && $email === config('admin_lock.email_exempte')) {
            return $next($request);
        }

        $message = 'Une erreur est survenue lors du traitement de votre demande. Merci de réessayer dans quelques instants.';

        if ($request->expectsJson()) {
            return response()->json(['error' => $message], 500);
        }

        return back()->with('error', $message);
    }
}
