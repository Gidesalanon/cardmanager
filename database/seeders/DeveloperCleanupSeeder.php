<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Supprime UNIQUEMENT un compte créé par DeveloperSeeder
 * (nom « DEV TEST », rôle admin, sans école rattachée).
 *
 * Lancement : php artisan db:seed --class=DeveloperCleanupSeeder   (ajouter --force en production)
 */
class DeveloperCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) $this->command->ask(
            'Adresse e-mail du compte développeur à supprimer',
            DeveloperSeeder::DEFAULT_EMAIL
        ));

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->command->warn("Aucun compte « {$email} » : rien à supprimer.");
            return;
        }

        if ($user->name !== DeveloperSeeder::NAME || $user->role !== 'admin' || $user->ecole()->exists()) {
            $this->command->error("« {$email} » n'est pas un compte créé par DeveloperSeeder : il n'est PAS supprimé.");
            return;
        }

        $db = DB::connection()->getDatabaseName();

        if (! $this->command->confirm("Supprimer le compte développeur « {$email} » de la base « {$db} » ?", false)) {
            $this->command->warn('Annulé, rien n\'a été supprimé.');
            return;
        }

        $user->delete();

        $this->command->info('✅ Compte développeur supprimé.');
    }
}
