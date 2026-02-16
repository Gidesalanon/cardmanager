<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Serie;

class SerieSeeder extends Seeder
{
    public function run(): void
    {
        $series = ['A', 'B', 'C', 'D', 'E', 'F', 'G1', 'G2', 'G3'];

        foreach ($series as $serie) {
            Serie::create(['nom' => $serie]);
        }
    }
}
