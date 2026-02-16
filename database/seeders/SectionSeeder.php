<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = ['maternelle', 'primaire', 'secondaire'];

        foreach ($sections as $nom) {
            Section::firstOrCreate([
                'nom' => $nom
            ]);
        }
    }
}
