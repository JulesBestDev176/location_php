<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocatairesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locataires')->insert([
            [
                'IdPersonne' => 3,
                'CNI' => '1234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
