<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            PersonnesTableSeeder::class,
            UtilisateursTableSeeder::class,
            AdministrateursTableSeeder::class,
            LocatairesTableSeeder::class,
            ProprietairesTableSeeder::class,
            AppartementsTableSeeder::class,
            LocationsTableSeeder::class,
            ModePaiementsTableSeeder::class,
            PaiementsTableSeeder::class,
        ]);
    }
}
