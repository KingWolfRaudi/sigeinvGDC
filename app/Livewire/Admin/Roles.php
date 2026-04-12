<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate; // Importante para la seguridad

class Roles extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $role_id, $name, $descripcion;
    public $tituloModal = 'Nuevo Rol';
    public $permisos_seleccionados = []; 
    public $searchPermiso = '';

    public function render()
    {
        $roles = Role::where('name', '!=', 'super-admin')->orderBy('id', 'asc')->paginate(10);
        
        $queryPermisos = Permission::orderBy('name', 'asc');
        
        if ($this->searchPermiso) {
            $queryPermisos->where('name', 'like', '%' . strtolower($this->searchPermiso) . '%');
        }
        $permisosAgrupados = [];
        
        foreach ($queryPermisos->get() as $permiso) {
            $name = $permiso->name;
            $accion_label = null;
            
            // Determinar macro categoría
            $macro = 'Catálogos';
            if (str_starts_with($name, 'movimientos-')) $macro = 'Movimientos';
            elseif ($name === 'admin-incidencias' || str_contains($name, 'solicitudes-perfil') || str_ends_with($name, '-usuarios') || str_ends_with($name, '-roles')) $macro = 'Administración';
            elseif (str_ends_with($name, '-trabajadores') || str_ends_with($name, '-departamentos')) $macro = 'Asignaciones';
            elseif (str_ends_with($name, '-computadores') || str_ends_with($name, '-dispositivos') || str_ends_with($name, '-insumos')) $macro = 'Inventarios';
            elseif (str_ends_with($name, '-incidencias')) $macro = 'Incidencias';
            elseif (str_starts_with($name, 'reportes-') || $name === 'admin-auditoria' || str_contains($name, 'auditoria')) $macro = 'Reportes y Auditoría';
            
            // Determinar entidad base
            $entidades = [
                'categorias-insumos'  => 'Categorías de Insumos',
                'sistemas-operativos' => 'Sistemas Operativos',
                'tipos-dispositivo'   => 'Tipos de Dispositivo',
                'computadores'        => 'Computadores',
                'dispositivos'        => 'Dispositivos',
                'departamentos'       => 'Departamentos',
                'trabajadores'        => 'Trabajadores',
                'procesadores'        => 'Procesadores',
                'puertos'             => 'Puertos',
                'insumos'             => 'Insumos',
                'marcas'              => 'Marcas',
                'gpus'                => 'GPUs',
                'usuarios'            => 'Usuarios',
                'roles'               => 'Roles',
                'problemas'           => 'Tipos de Incidencias',
                'incidencias'         => 'Gestión de Incidencias',
                'configuraciones'     => 'Configuraciones',
                'reportes'            => 'Módulo de Reportes',
                'auditoria'           => 'Auditoría y Logs',
            ];
            
            $entidadNombre = 'Otros';
            $accion = $name;
            
            if ($macro === 'Movimientos') {
                 if (str_contains($name, '-computadores-')) {
                     $entidadNombre = 'Movimientos Computadores';
                     $accion = str_replace('movimientos-computadores-', '', $name);
                 } elseif (str_contains($name, '-dispositivos-')) {
                     $entidadNombre = 'Movimientos Dispositivos';
                     $accion = str_replace('movimientos-dispositivos-', '', $name);
                 } elseif (str_contains($name, '-insumos-')) {
                     $entidadNombre = 'Movimientos Insumos';
                     $accion = str_replace('movimientos-insumos-', '', $name);
                 }
            } elseif ($macro === 'Reportes y Auditoría') {
                $entidadNombre = 'Generación de Reportes';
                if ($name === 'admin-auditoria') {
                    $entidadNombre = 'Auditoría';
                    $accion = 'Ver Logs';
                } else {
                    $accion = str_replace('reportes-', '', $name);
                }
            } elseif ($name === 'admin-solicitudes-perfil' || $name === 'admin-incidencias') {
                $entidadNombre = 'Configuraciones';
                $accion = $name;
                $accion_label = ($name === 'admin-solicitudes-perfil') ? 'Solicitudes de Perfil' : 'Configuraciones de Incidencias';
            } else {
                 foreach ($entidades as $key => $label) {
                     if (str_ends_with($name, $key)) {
                         $entidadNombre = $label;
                         $accion = str_replace('-' . $key, '', $name);
                         break;
                     }
                 }
            }
            
            // Generar la etiqueta si no fue asignada manualmente arriba
            if (!isset($accion_label)) {
                $accion_label = ucfirst(str_replace('-', ' ', $accion));
            }
            
            if (!isset($permisosAgrupados[$macro])) {
                $permisosAgrupados[$macro] = [];
            }
            if (!isset($permisosAgrupados[$macro][$entidadNombre])) {
                $permisosAgrupados[$macro][$entidadNombre] = [];
            }
            

            $permisosAgrupados[$macro][$entidadNombre][] = [
                'id' => $permiso->id,
                'name' => $permiso->name,
                'accion' => $accion,
                'label' => $accion_label
            ];
        }

        // --- ORDENAR PERMISOS POR PRIORIDAD --- (Solicitud del Usuario)
        $prioridades = [
            'cambiar-estatus'   => 1,
            'crear'             => 2,
            'editar'            => 3,
            'eliminar'          => 4,
            'ver-estado'        => 5,
            'ver'               => 6,
            // Movimientos
            'enviar'            => 7,
            'aprobar'           => 8,
            'rechazar'          => 9,
            'ejecutar-directo'  => 10,
            // Reportes
            'excel'             => 11,
            'pdf'               => 12,
            'masivos-filtros'   => 13,
            'Ver Logs'          => 14,
        ];

        foreach ($permisosAgrupados as $macro => &$subgrupos) {
            foreach ($subgrupos as $entidad => &$permisos) {
                usort($permisos, function($a, $b) use ($prioridades) {
                    $prioA = $prioridades[$a['accion']] ?? 99;
                    $prioB = $prioridades[$b['accion']] ?? 99;
                    
                    if ($prioA === $prioB) {
                        return strcmp($a['label'], $b['label']);
                    }
                    return $prioA <=> $prioB;
                });
            }
        }
        // ------------------------------------
        
        // Pasamos $permisosAgrupados a la vista en lugar del listado plano
        return view('livewire.admin.roles', compact('roles', 'permisosAgrupados'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-roles'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Rol';
        $this->dispatch('abrir-modal', id: 'modalRol');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->role_id ? 'editar-roles' : 'crear-roles'), 403);

        $this->validate([
            'name' => 'required|min:2|unique:roles,name,' . $this->role_id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        if ($this->role_id) {
            $rolActual = Role::find($this->role_id);
            if ($rolActual->name === 'super-admin' && $this->name !== 'super-admin') {
                $this->dispatch('mostrar-toast', mensaje: 'No puedes modificar la base del Super Admin.', tipo: 'danger');
                return;
            }
        }

        $rol = Role::updateOrCreate(
            ['id' => $this->role_id],
            [
                'name' => strtolower(str_replace(' ', '-', $this->name)),
                'guard_name' => 'web',
                'descripcion' => $this->descripcion
            ]
        );

        if ($rol->name !== 'super-admin') {
            $rol->syncPermissions($this->permisos_seleccionados);
        }

        $this->dispatch('cerrar-modal', id: 'modalRol');
        $this->dispatch('mostrar-toast', mensaje: $this->role_id ? 'Rol y permisos actualizados.' : 'Rol creado exitosamente.', tipo: 'success');
        
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-roles'), 403);

        $this->resetValidation();
        $rol = Role::findOrFail($id);
        $this->role_id = $rol->id;
        $this->name = $rol->name;
        $this->descripcion = $rol->descripcion;
        
        $this->permisos_seleccionados = $rol->permissions->pluck('name')->toArray();
        
        $this->tituloModal = 'Editar Rol';
        $this->dispatch('abrir-modal', id: 'modalRol');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-roles'), 403);

        $rol = Role::findOrFail($id);
        
        if ($rol->name === 'super-admin') {
            $this->dispatch('mostrar-toast', mensaje: 'El rol Super Admin no puede ser eliminado.', tipo: 'danger');
            return;
        }

        $rol->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Rol eliminado exitosamente.', tipo:'success');
    }

    public function resetCampos()
    {
        $this->reset(['role_id', 'name', 'descripcion', 'permisos_seleccionados', 'searchPermiso']);
        $this->resetValidation();
    }
}