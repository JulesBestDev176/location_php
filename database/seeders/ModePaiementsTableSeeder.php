<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModePaiementsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modepaiements')->insert([
            [
                'LibelleModePaiement' => 'Espèces',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'LibelleModePaiement' => 'Virement',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
