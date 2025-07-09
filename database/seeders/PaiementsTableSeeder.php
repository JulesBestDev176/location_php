<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaiementsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('paiements')->insert([
            [
                'DatePaiement' => now(),
                'MontantPaiement' => 1200,
                'NumeroFacture' => 'FACT001',
                'Statut' => true,
                'IdLocation' => 1,
                'IdModePaiement' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
