<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppartementsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('appartements')->insert([
            [
                'AdresseAppartement' => '123 rue de Paris',
                'Surface' => 80.5,
                'NombrePiece' => 3,
                'Capacite' => 4,
                'Disponiblite' => true,
                'nbrLocataire' => 1,
                'IdProprietaire' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
