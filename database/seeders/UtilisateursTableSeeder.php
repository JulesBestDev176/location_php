<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UtilisateursTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('utilisateurs')->insert([
            [
                'IdPersonne' => 2,
                'Identifiant' => 'jdupont',
                'MotDePasse' => Hash::make('password123A'),
                'profil' => 'Utilisateur',
                'Statut' => 'Actif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'IdPersonne' => 3,
                'Identifiant' => 'cmartin',
                'MotDePasse' => Hash::make('password123A'),
                'profil' => 'Utilisateur',
                'Statut' => 'Actif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
