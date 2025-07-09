<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonnesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('personnes')->insert([
            [
                'Nom' => 'Dupont',
                'Prenom' => 'Jean',
                'Telephone' => '771111111',
                'Email' => 'jean.dupont@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Nom' => 'Martin',
                'Prenom' => 'Claire',
                'Telephone' => '772222222',
                'Email' => 'claire.martin@email.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
