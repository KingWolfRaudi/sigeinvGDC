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
            
            // Agregamos semillas para el inventario real (Equipos y Dispositivos)
            InventarioSeeder::class,
            
            // Agregamos el seeder de Incidencias y Perfil
            IncidenciasSeeder::class,
            
            // Agregamos Demo Data para Incidentes
            DemoTicketsSeeder::class,
        ]);
    }
}