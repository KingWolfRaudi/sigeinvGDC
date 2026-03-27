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

    public function render()
    {
        $roles = Role::orderBy('id', 'asc')->paginate(10);
        
        // Magia de Colecciones: Agrupamos los permisos por la última palabra de su nombre.
        // Ej: de 'crear-marcas' o 'cambiar-estatus-marcas', el grupo será 'Marcas'.
        $permisosAgrupados = Permission::orderBy('name', 'asc')->get()->groupBy(function($permiso) {
            $partes = explode('-', $permiso->name);
            return ucfirst(end($partes)); 
        });
        
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
                $this->dispatch('mostrar-toast', mensaje: 'No puedes modificar la base del Super Admin.');
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
        $this->dispatch('mostrar-toast', mensaje: $this->role_id ? 'Rol y permisos actualizados.' : 'Rol creado exitosamente.');
        
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
            $this->dispatch('mostrar-toast', mensaje: 'El rol Super Admin no puede ser eliminado.');
            return;
        }

        $rol->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Rol eliminado exitosamente.');
    }

    public function resetCampos()
    {
        $this->reset(['role_id', 'name', 'descripcion', 'permisos_seleccionados']);
        $this->resetValidation();
    }
}