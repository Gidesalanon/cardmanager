<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ecole;
use App\Models\SchoolYear;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestEcoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer une année scolaire active
        $schoolYear = SchoolYear::create([
            'label' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'is_active' => true,
        ]);

        // 2. Créer un utilisateur pour l'école
        $user = User::create([
            'name' => 'Directeur Test',
            'email' => 'directeur@test-ecole.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'), // Mot de passe simple pour les tests
            'role' => 'ecole',
            'remember_token' => Str::random(10),
        ]);

        // 3. Créer l'école
        $ecole = Ecole::create([
            'nom_ecole' => 'École Primaire Test CardManager',
            'adresse_ecole' => '123 Rue de l\'Éducation, 75001 Paris',
            'telephone' => '01 23 45 67 89',
            'numero_autorisation' => 'AUT-2025-TEST-001',
            'user_id' => $user->id,
        ]);

        // 4. Afficher les informations de connexion
        $this->command->info('✅ École de test créée avec succès !');
        $this->command->info('📧 Email: directeur@test-ecole.com');
        $this->command->info('🔑 Mot de passe: password123');
        $this->command->info('🏫 École: ' . $ecole->nom_ecole);
        $this->command->info('📅 Année scolaire: ' . $schoolYear->label . ' (active)');
        
        $this->command->warn('⚠️  Pensez à changer le mot de passe en production !');
    }
}
