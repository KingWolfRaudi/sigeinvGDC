<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Roles, Permisos y Usuario Super Admin
            RolesAndPermissionsSeeder::class,
            
            // 2. Catálogos base (Marcas, Tipos, SO, Departamentos, etc.)
            CatalogosSeeder::class,
            
            // 3. Configuraciones de Incidencias, Perfil y Especialidades
            IncidenciasSeeder::class,
        ]);
    }
}