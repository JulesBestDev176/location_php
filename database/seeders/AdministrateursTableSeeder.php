<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdministrateursTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('administrateurs')->insert([
            [
                'IdPersonne' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
