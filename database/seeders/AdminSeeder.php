<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insérer la personne
        $idPersonne = DB::table('personnes')->insertGetId([
            'Nom' => 'Admin',
            'Prenom' => 'Admin',
            'Telephone' => '770000000',
            'Email' => 'admin@admin.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Log::info('AdminSeeder: Personne insérée', ['IdPersonne' => $idPersonne]);

        // Insérer l'utilisateur
        DB::table('utilisateurs')->insert([
            'IdPersonne' => $idPersonne,
            'Identifiant' => 'Admin',
            'MotDePasse' => Hash::make('Admin'),
            'profil' => 'Administrateur',
            'Statut' => 'Actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Log::info('AdminSeeder: Utilisateur inséré', ['Identifiant' => 'Admin']);

        // Insérer l'administrateur
        DB::table('administrateurs')->insert([
            'IdPersonne' => $idPersonne,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Log::info('AdminSeeder: Administrateur inséré', ['IdPersonne' => $idPersonne]);
    }
}
