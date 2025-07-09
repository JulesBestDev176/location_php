<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locations')->insert([
            [
                'NumeroLocation' => 'LOC001',
                'MontantLocation' => 1200,
                'DateDebut' => '2024-01-01',
                'DateFin' => null,
                'DateCreation' => now(),
                'Statut' => true,
                'IdAppartement' => 1,
                'IdLocataire' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
