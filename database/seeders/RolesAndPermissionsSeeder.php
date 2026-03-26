<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; // <-- Asegúrate de importar nuestro nuevo modelo
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear la caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear los roles con su descripción
        $roles = [
            ['name' => 'super-admin', 'descripcion' => 'Control total del sistema y configuraciones'],
            ['name' => 'administrador', 'descripcion' => 'Gestión completa de inventario y personal'],
            ['name' => 'coordinador', 'descripcion' => 'Supervisión de equipos y movimientos'],
            ['name' => 'personal-ti', 'descripcion' => 'Gestión operativa de equipos técnicos'],
            ['name' => 'trabajador', 'descripcion' => 'Usuario estándar para asignación de equipos'],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['name' => $rol['name']], $rol);
        }

        // 2. Crear el usuario SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sigeinv.test'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );

        // 3. Asignar el rol al SuperAdmin
        $superAdmin->assignRole('super-admin');
    }
}