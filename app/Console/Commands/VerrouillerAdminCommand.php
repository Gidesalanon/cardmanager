<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VerrouillerAdminCommand extends Command
{
    protected $signature = 'admin:verrouiller';
    protected $description = 'Bloque temporairement la génération des cartes et les imports pour l\'admin principal (le compte développeur garde l\'accès)';

    public function handle(): int
    {
        $fichier = storage_path('framework/admin-actions.locked');
        file_put_contents($fichier, now()->toDateTimeString() . ' par ' . (get_current_user() ?: 'cli') . "\n");

        $this->info('Verrou activé : génération des cartes et import bloqués pour l\'admin principal.');
        $this->line('Le compte "' . config('admin_lock.email_exempte') . '" garde l\'accès.');
        $this->warn('Pour retirer ce verrou : php artisan admin:deverrouiller');

        return self::SUCCESS;
    }
}
