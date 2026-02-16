<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Partition;

class PartitionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Classe::all() as $classe) {
            for ($i = 1; $i <= 2; $i++) {
                Partition::firstOrCreate([
                    'classe_id' => $classe->id,
                    'nom' => $classe->nom . $i,
                ]);
            }
        }
    }
}
