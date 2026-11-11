<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Crée (ou réinitialise le mot de passe de) un compte ADMINISTRATEUR de développement,
 * distinct du compte admin habituel.
 *
 * ⚠️  Ce compte a tous les droits d'un administrateur (il voit et modifie les vraies
 *     données si la base est celle de production). À supprimer après usage.
 *
 * Garde-fous :
 *  - aucun mot de passe dans le code : généré au hasard, affiché UNE seule fois
 *  - confirmation obligatoire, avec affichage de la base et de l'environnement visés
 *  - ne modifie jamais un compte existant qui n'a pas été créé par ce seeder
 *  - e-mail par défaut en .test (domaine réservé, personne ne peut recevoir un
 *    e-mail de réinitialisation de mot de passe à cette adresse)
 *  - n'est PAS appelé par DatabaseSeeder : il faut le lancer explicitement
 *
 * Lancement :   php artisan db:seed --class=DeveloperSeeder          (ajouter --force en production)
 * Suppression : php artisan db:seed --class=DeveloperCleanupSeeder   (ajouter --force en production)
 */
class DeveloperSeeder extends Seeder
{
    public const NAME          = 'DEV TEST';
    public const DEFAULT_EMAIL = 'dev@cardmanager.test';

    public function run(): void
    {
        $email = trim((string) $this->command->ask(
            'Adresse e-mail du compte développeur',
            self::DEFAULT_EMAIL
        ));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command->error("Adresse e-mail invalide : « {$email} ». Rien n'a été créé.");
            return;
        }

        $db  = DB::connection()->getDatabaseName();
        $env = app()->environment();

        $this->command->warn('Ce seeder crée un compte ADMINISTRATEUR complet.');

        if (! $this->command->confirm(
            "Base « {$db} » (environnement : {$env}). Créer ou réinitialiser le compte « {$email} » ?",
            false
        )) {
            $this->command->warn('Annulé, rien n\'a été créé.');
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user && $user->name !== self::NAME) {
            $this->command->error("Un compte « {$email} » existe déjà et n'a pas été créé par ce seeder : il n'est PAS modifié.");
            return;
        }

        $password = Str::random(20);

        $user = $user ?? new User(['email' => $email]);
        $user->forceFill([
            'name'              => self::NAME,
            'password'          => Hash::make($password),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ])->save();

        $this->command->info('✅ Compte développeur prêt.');
        $this->command->info('📧 Identifiant   : ' . $email);
        $this->command->info('🔑 Mot de passe  : ' . $password . '   (affiché une seule fois)');
        $this->command->warn('Note-le dans un gestionnaire de mots de passe. Ne le colle dans aucune conversation.');
        $this->command->warn('Suppression : php artisan db:seed --class=DeveloperCleanupSeeder');
    }
}
