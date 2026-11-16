<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'contact@donami.bj';

        // Un compte existant n'est jamais modifié (ni son mot de passe, ni son rôle)
        if (User::where('email', $email)->exists()) {
            $this->command->info("Compte administrateur {$email} déjà présent : inchangé.");
            return;
        }

        // Aucun mot de passe n'est écrit dans le code : il est généré et affiché une seule fois
        $password = Str::random(20);

        User::create([
            'name'              => 'DONAMI-CHRIST Admin',
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info("Compte administrateur créé : {$email}");
        $this->command->warn("Mot de passe (affiché une seule fois) : {$password}");
    }
}
