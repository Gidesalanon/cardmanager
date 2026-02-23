<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Ecole;
use App\Models\SchoolYear;

class TestClassesSeeder extends Seeder
{
    public function run(): void
    {
        $ecole = Ecole::first();
        $schoolYear = SchoolYear::where('is_active', true)->first();

        if (!$ecole || !$schoolYear) {
            $this->command->error('❌ École ou année scolaire non trouvée. Exécutez d\'abord TestEcoleSeeder.');
            return;
        }

        // Classes de test pour l'école
        $classes = [
            [
                'nom' => 'CP A',
                'section' => 'Primaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CP B',
                'section' => 'Primaire',
                'serie' => 'B',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CE1 A',
                'section' => 'Primaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CE1 B',
                'section' => 'Primaire',
                'serie' => 'B',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CE2 A',
                'section' => 'Primaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CM1 A',
                'section' => 'Primaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => 'CM2 A',
                'section' => 'Primaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => '6ème A',
                'section' => 'Secondaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => '5ème A',
                'section' => 'Secondaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
            [
                'nom' => '4ème A',
                'section' => 'Secondaire',
                'serie' => 'A',
                'ecole_id' => $ecole->id,
                'school_year_id' => $schoolYear->id,
            ],
        ];

        foreach ($classes as $classeData) {
            Classe::create($classeData);
        }

        $this->command->info('✅ ' . count($classes) . ' classes de test créées pour l\'école ' . $ecole->nom_ecole);
        $this->command->info('📚 Classes disponibles: CP, CE1, CE2, CM1, CM2, 6ème, 5ème, 4ème');
    }
}
