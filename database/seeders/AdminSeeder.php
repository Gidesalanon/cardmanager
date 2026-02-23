<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'serveodeal@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(), // ✅ email déjà vérifié
            ]
        );
    }
}
