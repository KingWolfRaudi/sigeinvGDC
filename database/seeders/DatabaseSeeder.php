<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            // UserSeeder::class, <- (Si lo tienes separado, si no, omítelo)
            
            // Agregamos nuestro nuevo seeder de catálogos
            CatalogosSeeder::class,
        ]);
    }
}