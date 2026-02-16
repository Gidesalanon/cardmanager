<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\Classe;
use App\Models\Serie;

class ClasseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Section::all() as $section) {

            $classes = [];

            if ($section->nom === 'maternelle') {
                $classes = ['PS', 'GS'];
            }

            if ($section->nom === 'primaire') {
                $classes = ['CI', 'CP', 'CE1', 'CE2', 'CM1', 'CM2'];
            }

            if ($section->nom === 'secondaire') {
                $classes = ['6e', '5e', '4e', '3e', '2nde', '1ère', 'Tle'];
                $series = Serie::all();
            }

            foreach ($classes as $classeNom) {
                if ($section->nom === 'secondaire') {
                    foreach ($series as $serie) {
                        Classe::firstOrCreate([
                            'section_id' => $section->id,
                            'nom'        => $classeNom,
                            'serie_id'   => $serie->id,
                        ]);
                    }
                } else {
                    Classe::firstOrCreate([
                        'section_id' => $section->id,
                        'nom'        => $classeNom,
                        'serie_id'   => null,
                    ]);
                }
            }
        }
    }
}
