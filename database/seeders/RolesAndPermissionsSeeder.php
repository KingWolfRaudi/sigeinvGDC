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

        // 2. CREAR LOS PERMISOS (CON DESCRIPCIONES)
        $entidades = [
            'marcas' => 'Marcas',
            'usuarios' => 'Usuarios',
            'roles' => 'Roles',
            'tipos-dispositivo' => 'Tipos de Dispositivo',
            'sistemas-operativos' => 'Sistemas Operativos',
            'puertos' => 'Puertos',
            'departamentos' => 'Departamentos',
            'procesadores' => 'Procesadores',
            'gpus' => 'GPUs',
            'trabajadores' => 'Trabajadores',
            'computadores' => 'Computadores',
            'dispositivos' => 'Dispositivos',
            'categorias-insumos' => 'Categorías de Insumos',
            'insumos' => 'Insumos',
            'software' => 'Software',
            'problemas' => 'Tipos de Incidencias',
            'especialidades' => 'Especialidades Técnicas',
        ];

        $acciones = [
            'ver' => 'Permite visualizar el listado de ',
            'crear' => 'Permite registrar nuevos registros de ',
            'editar' => 'Permite modificar datos de los registros de ',
            'cambiar-estatus' => 'Permite activar o desactivar registros de ',
            'ver-estado' => 'Permite ver el estatus (activo/inactivo) de ',
            'eliminar' => 'Permite borrar registros (borrado lógico) de ',
        ];

        $permisosFinales = [];

        // Generar permisos CRUD automáticos
        foreach ($entidades as $slug => $nombre) {
            foreach ($acciones as $accion => $descBase) {
                $name = "{$accion}-{$slug}";
                // Algunos casos especiales de nombres
                if ($slug === 'roles' && in_array($accion, ['cambiar-estatus', 'ver-estado'])) continue;
                
                $permisosFinales[$name] = $descBase . $nombre . '.';
            }
        }

        // Permisos Especiales Manuales
        $especiales = [
            'movimientos-computadores-crear' => 'Permite crear nuevos movimientos de computadores.',
            'movimientos-computadores-ver' => 'Permite visualizar el historial de movimientos de computadores.',
            'movimientos-computadores-enviar' => 'Permite enviar movimientos de computadores para su aprobación.',
            'movimientos-computadores-aprobar' => 'Permite aprobar movimientos de computadores solicitados.',
            'movimientos-computadores-rechazar' => 'Permite rechazar movimientos de computadores solicitados.',
            'movimientos-computadores-ejecutar-directo' => 'Permite ejecutar movimientos de computadores sin pasar por aprobación (Admin).',

            'movimientos-dispositivos-crear' => 'Permite crear nuevos movimientos de dispositivos.',
            'movimientos-dispositivos-ver' => 'Permite visualizar el historial de movimientos de dispositivos.',
            'movimientos-dispositivos-enviar' => 'Permite enviar movimientos de dispositivos para su aprobación.',
            'movimientos-dispositivos-aprobar' => 'Permite aprobar movimientos de dispositivos solicitados.',
            'movimientos-dispositivos-rechazar' => 'Permite rechazar movimientos de dispositivos solicitados.',
            'movimientos-dispositivos-ejecutar-directo' => 'Permite ejecutar movimientos de dispositivos sin pasar por aprobación (Admin).',

            'movimientos-insumos-crear' => 'Permite crear nuevos movimientos de insumos.',
            'movimientos-insumos-ver' => 'Permite visualizar el historial de movimientos de insumos.',
            'movimientos-insumos-enviar' => 'Permite enviar movimientos de insumos para su aprobación.',
            'movimientos-insumos-aprobar' => 'Permite aprobar movimientos de insumos solicitados.',
            'movimientos-insumos-rechazar' => 'Permite rechazar movimientos de insumos solicitados.',
            'movimientos-insumos-ejecutar-directo' => 'Permite ejecutar movimientos de insumos sin pasar por aprobación (Admin).',

            'crear-ticket' => 'Permite reportar una nueva incidencia desde el portal de usuario.',
            'gestionar-incidencias' => 'Acceso a la mesa de soporte para atender, asignar y resolver tickets.',
            'admin-incidencias' => 'Configuraciones globales y administración avanzada del módulo de incidencias.',
            'ver-incidencias' => 'Permite visualizar el histórico detallado de incidencias.',
            'admin-solicitudes-perfil' => 'Gestionar y aprobar solicitudes de cambio de perfil técnico de usuarios.',
            
            'admin-auditoria' => 'Acceso total a los registros de auditoría y logs detallados del sistema.',
            'ver-auditoria-tecnicos' => 'Permite visualizar la vista de auditoría de técnicos.',
            'ver-auditoria-usuarios' => 'Permite visualizar la vista de auditoría individual de usuarios.',
            'reportes-excel' => 'Permite exportar datos y listados a formato Microsoft Excel.',
            'reportes-pdf' => 'Permite generar y descargar reportes en formato PDF.',
            'reportes-masivos-filtros' => 'Acceso a herramientas de filtrado avanzado para reportes masivos personalizados.',
            'ver-dashboard' => 'Permite visualizar el panel de control principal (Dashboard).',
            'ver-panel-soporte' => 'Permite visualizar el acceso al panel de soporte en el menú lateral.'
        ];

        $permisosFinales = array_merge($permisosFinales, $especiales);

        foreach ($permisosFinales as $name => $desc) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['descripcion' => $desc]
            );
        }

        // 2. CREAR LOS ROLES (Tu código original)
        $roles = [
            ['name' => 'super-admin', 'descripcion' => 'Control total del sistema y configuraciones'],
            ['name' => 'administrador', 'descripcion' => 'Gestión completa de inventario y personal'],
            ['name' => 'coordinador', 'descripcion' => 'Supervisión de equipos y movimientos'],
            ['name' => 'personal-ti', 'descripcion' => 'Gestión operativa de equipos técnicos'],
            ['name' => 'resolutor-incidencia', 'descripcion' => 'Especialista en resolución de fallas asignadas'],
            ['name' => 'trabajador', 'descripcion' => 'Usuario estándar para asignación de equipos'],
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['name' => $rol['name']], $rol);
        }

        // 3. ASIGNAR PERMISOS INICIALES
        $todosLosPermisos = Permission::all();

        // --- SUPER ADMIN: Todo ---
        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions($todosLosPermisos);
        }

        // --- ADMINISTRADOR: Todo menos eliminar ---
        $adminRole = Role::where('name', 'administrador')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($todosLosPermisos->filter(fn($p) => !str_starts_with($p->name, 'eliminar-')));
        }

        // Grupos de Slugs para facilitar la asignación
        $catalogos = ['marcas', 'tipos-dispositivo', 'sistemas-operativos', 'puertos', 'procesadores', 'gpus', 'categorias-insumos', 'problemas'];
        $asignaciones = ['departamentos', 'trabajadores', 'dependencias'];
        $inventario = ['computadores', 'dispositivos', 'insumos', 'software'];
        $catAsigInv = array_merge($catalogos, $asignaciones, $inventario);

        // --- COORDINADOR ---
        $coordinadorRole = Role::where('name', 'coordinador')->first();
        if ($coordinadorRole) {
            $coorPerms = $todosLosPermisos->filter(function($p) use ($catAsigInv) {
                // Dashboard y Panel de Soporte
                if (in_array($p->name, ['ver-dashboard', 'ver-panel-soporte'])) return true;

                // Reportes y Auditoría específica
                if (in_array($p->name, ['reportes-excel', 'reportes-pdf', 'reportes-masivos-filtros', 'ver-auditoria-tecnicos'])) return true;
                
                // Incidencias (Especiales)
                if (in_array($p->name, ['crear-ticket', 'gestionar-incidencias', 'admin-incidencias', 'ver-incidencias', 'admin-solicitudes-perfil'])) return true;
                
                // Movimientos (Todos)
                if (str_starts_with($p->name, 'movimientos-')) return true;
                
                // Catálogos, Asignaciones e Inventario (menos eliminar)
                foreach ($catAsigInv as $slug) {
                    if (str_ends_with($p->name, "-$slug") && !str_starts_with($p->name, 'eliminar-')) return true;
                }

                // Especialidades Técnicas (menos eliminar)
                if (str_ends_with($p->name, '-especialidades') && !str_starts_with($p->name, 'eliminar-')) return true;

                return false;
            });
            $coordinadorRole->syncPermissions($coorPerms);
        }

        // --- PERSONAL-TI ---
        $personalTiRole = Role::where('name', 'personal-ti')->first();
        if ($personalTiRole) {
            $tiPerms = $todosLosPermisos->filter(function($p) use ($catAsigInv) {
                // Dashboard y Panel de Soporte
                if (in_array($p->name, ['ver-dashboard', 'ver-panel-soporte'])) return true;

                // Todos los reportes
                if (in_array($p->name, ['reportes-excel', 'reportes-pdf', 'reportes-masivos-filtros'])) return true;
                
                // Incidencias (Especiales) menos solicitudes de perfil
                if (in_array($p->name, ['crear-ticket', 'gestionar-incidencias', 'admin-incidencias', 'ver-incidencias'])) return true;
                
                // Todos los movimientos
                if (str_starts_with($p->name, 'movimientos-')) return true;
                
                // Catálogos, Asignaciones e Inventario (menos eliminar)
                foreach ($catAsigInv as $slug) {
                    if (str_ends_with($p->name, "-$slug") && !str_starts_with($p->name, 'eliminar-')) return true;
                }

                return false;
            });
            $personalTiRole->syncPermissions($tiPerms);
        }

        // --- RESOLUTOR-INCIDENCIA ---
        $resolutorRole = Role::where('name', 'resolutor-incidencia')->first();
        if ($resolutorRole) {
            $resolutorRole->syncPermissions(['ver-incidencias', 'gestionar-incidencias', 'ver-dashboard', 'ver-panel-soporte']);
        }

        // --- TRABAJADOR ---
        $trabajadorRole = Role::where('name', 'trabajador')->first();
        if ($trabajadorRole) {
            $trabajadorRole->syncPermissions(['crear-ticket', 'ver-dashboard', 'ver-panel-soporte']);
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