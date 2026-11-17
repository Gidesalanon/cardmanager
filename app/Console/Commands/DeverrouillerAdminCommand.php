<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeverrouillerAdminCommand extends Command
{
    protected $signature = 'admin:deverrouiller';
    protected $description = 'Retire le verrou pose par admin:verrouiller : tout redevient comme avant, immediatement';

    public function handle(): int
    {
        $fichier = storage_path('framework/admin-actions.locked');

        if (!is_file($fichier)) {
            $this->info('Aucun verrou actif : rien à faire.');
            return self::SUCCESS;
        }

        unlink($fichier);
        $this->info('Verrou retiré : la génération des cartes et l\'import sont de nouveau disponibles pour tous les administrateurs.');

        return self::SUCCESS;
    }
}
