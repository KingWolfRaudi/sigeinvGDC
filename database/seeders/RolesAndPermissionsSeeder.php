<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role; 
use App\Models\User;
use Spatie\Permission\Models\Permission; // <-- IMPORTANTE: Importamos el modelo de Permisos
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear la caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. CREAR LOS PERMISOS (NUEVO)
        $permisos = [
            // Marcas
            'ver-marcas', 'crear-marcas', 'editar-marcas', 'cambiar-estatus-marcas', 'eliminar-marcas',
            // Usuarios
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'cambiar-estatus-usuarios', 'eliminar-usuarios',
            // Roles
            'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles',
            // Tipos de Dispositivo
            'ver-tipos-dispositivo', 'crear-tipos-dispositivo', 'editar-tipos-dispositivo', 'cambiar-estatus-tipos-dispositivo', 'eliminar-tipos-dispositivo',
            // Sistemas Operativos
            'ver-sistemas-operativos', 'crear-sistemas-operativos', 'editar-sistemas-operativos', 'cambiar-estatus-sistemas-operativos', 'eliminar-sistemas-operativos',
            // Puertos
            'ver-puertos', 'crear-puertos', 'editar-puertos', 'cambiar-estatus-puertos', 'eliminar-puertos',
            // Departamentos
            'ver-departamentos', 'crear-departamentos', 'editar-departamentos', 'cambiar-estatus-departamentos', 'eliminar-departamentos',
            // Procesadores
            'ver-procesadores', 'crear-procesadores', 'editar-procesadores', 'cambiar-estatus-procesadores', 'eliminar-procesadores',
            // Gpus
            'ver-gpus', 'crear-gpus', 'editar-gpus', 'cambiar-estatus-gpus', 'eliminar-gpus'
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 2. CREAR LOS ROLES (Tu código original)
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

        // 3. ASIGNAR PERMISOS INICIALES (NUEVO)
        // Le damos todos los permisos al rol 'administrador' de arranque para que no esté vacío
        $adminRole = Role::where('name', 'administrador')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // 4. CREAR EL USUARIO SUPERADMIN (Tu código original mejorado)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sigeinv.test'],
            [
                'name' => 'Super Administrador',
                'username' => 'superadmin', // <-- Agregamos el username para el nuevo Login
                'password' => Hash::make('password'),
                'activo' => true,
            ]
        );

        // 5. ASIGNAR ROL AL SUPERADMIN
        $superAdmin->assignRole('super-admin');
    }
}