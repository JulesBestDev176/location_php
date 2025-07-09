<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProprietairesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('proprietaires')->insert([
            [
                'IdPersonne' => 2,
                'Ninea' => 'NINEA123',
                'Rccm' => 'RCCM123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
