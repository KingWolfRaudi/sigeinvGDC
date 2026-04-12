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

        // 1. CREAR EL USUARIO SISTEMA (DEBE SER ID 1 PARA EL TRAIT)
        if (!\Illuminate\Support\Facades\DB::table('users')->where('id', 1)->exists()) {
            \Illuminate\Support\Facades\DB::table('users')->insert([
                'id' => 1,
                'name' => 'Sistema',
                'username' => 'sistema',
                'email' => 'sistema@sigeinv.test',
                'password' => Hash::make(\Illuminate\Support\Str::random(32)),
                'activo' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. CREAR LOS PERMISOS (NUEVO)
        $permisos = [
            // Marcas
            'ver-marcas', 'crear-marcas', 'editar-marcas', 'cambiar-estatus-marcas', 'ver-estado-marcas', 'eliminar-marcas',
            // Usuarios
            'ver-usuarios', 'crear-usuarios', 'editar-usuarios', 'cambiar-estatus-usuarios', 'ver-estado-usuarios', 'eliminar-usuarios',
            // Roles (Generalmente no tienen estatus activo/inactivo)
            'ver-roles', 'crear-roles', 'editar-roles', 'eliminar-roles',
            // Tipos de Dispositivo
            'ver-tipos-dispositivo', 'crear-tipos-dispositivo', 'editar-tipos-dispositivo', 'cambiar-estatus-tipos-dispositivo', 'ver-estado-tipos-dispositivo', 'eliminar-tipos-dispositivo',
            // Sistemas Operativos
            'ver-sistemas-operativos', 'crear-sistemas-operativos', 'editar-sistemas-operativos', 'cambiar-estatus-sistemas-operativos', 'ver-estado-sistemas-operativos', 'eliminar-sistemas-operativos',
            // Puertos
            'ver-puertos', 'crear-puertos', 'editar-puertos', 'cambiar-estatus-puertos', 'ver-estado-puertos', 'eliminar-puertos',
            // Departamentos
            'ver-departamentos', 'crear-departamentos', 'editar-departamentos', 'cambiar-estatus-departamentos', 'ver-estado-departamentos', 'eliminar-departamentos',
            // Procesadores
            'ver-procesadores', 'crear-procesadores', 'editar-procesadores', 'cambiar-estatus-procesadores', 'ver-estado-procesadores', 'eliminar-procesadores',
            // Gpus
            'ver-gpus', 'crear-gpus', 'editar-gpus', 'cambiar-estatus-gpus', 'ver-estado-gpus', 'eliminar-gpus',
            // Trabajadores (Faltaban ambos)
            'ver-trabajadores', 'crear-trabajadores', 'editar-trabajadores', 'cambiar-estatus-trabajadores', 'ver-estado-trabajadores', 'eliminar-trabajadores',
            // Computadores (Faltaban ambos)
            'ver-computadores', 'crear-computadores', 'editar-computadores', 'cambiar-estatus-computadores', 'ver-estado-computadores', 'eliminar-computadores',
            // Dispositivos
            'ver-dispositivos', 'crear-dispositivos', 'editar-dispositivos', 'cambiar-estatus-dispositivos', 'ver-estado-dispositivos', 'eliminar-dispositivos',
            // Categoria Insumos
            'ver-categorias-insumos', 'crear-categorias-insumos', 'editar-categorias-insumos', 'cambiar-estatus-categorias-insumos', 'ver-estado-categorias-insumos', 'eliminar-categorias-insumos',
            // Insumos
            'ver-insumos', 'crear-insumos', 'editar-insumos', 'cambiar-estatus-insumos', 'ver-estado-insumos', 'eliminar-insumos',
            // Software
            'ver-software', 'crear-software', 'editar-software', 'cambiar-estatus-software', 'ver-estado-software', 'eliminar-software',

            // ── Movimientos Computadores ──────────────────────────────────────
            'movimientos-computadores-crear',
            'movimientos-computadores-ver',
            'movimientos-computadores-enviar',
            'movimientos-computadores-aprobar',
            'movimientos-computadores-rechazar',
            'movimientos-computadores-ejecutar-directo',

            // ── Movimientos Dispositivos ──────────────────────────────────────
            'movimientos-dispositivos-crear',
            'movimientos-dispositivos-ver',
            'movimientos-dispositivos-enviar',
            'movimientos-dispositivos-aprobar',
            'movimientos-dispositivos-rechazar',
            'movimientos-dispositivos-ejecutar-directo',

            // ── Movimientos Insumos ───────────────────────────────────────────
            'movimientos-insumos-crear',
            'movimientos-insumos-ver',
            'movimientos-insumos-enviar',
            'movimientos-insumos-aprobar',
            'movimientos-insumos-rechazar',
            'movimientos-insumos-ejecutar-directo',

            // ── Problemas (Catálogo de Incidencias) ──────────────────────────
            'ver-problemas', 'crear-problemas', 'editar-problemas', 'cambiar-estatus-problemas', 'ver-estado-problemas', 'eliminar-problemas',

            // ── Incidencias ──────────────────────────────────────────────────
            'ver-incidencias', 'crear-incidencias', 'editar-incidencias', 'cerrar-incidencias', 'ver-estado-incidencias', 'eliminar-incidencias',
            'admin-incidencias', 'admin-solicitudes-perfil',

            // ── Reportes y Auditoría ──────────────────────────────────────────
            'admin-auditoria', 'reportes-excel', 'reportes-pdf', 'reportes-masivos-filtros'
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

        // 3. ASIGNAR PERMISOS INICIALES
        $todosLosPermisos = Permission::all();
        
        $adminRole = Role::where('name', 'administrador')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($todosLosPermisos);
        }

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions($todosLosPermisos);
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