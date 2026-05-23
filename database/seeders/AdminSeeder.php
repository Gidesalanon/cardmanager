<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'contact@donami.bj'],
            [
                'name'               => 'DONAMI-CHRIST Admin',
                'password'           => bcrypt('Don@mi2026'),
                'role'               => 'admin',
                'email_verified_at'  => now(),
            ]
        );
    }
}