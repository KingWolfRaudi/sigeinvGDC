<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Role; // Usamos nuestro modelo personalizado de la Fase 1
use Livewire\WithPagination;

class Roles extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $role_id, $name, $descripcion;
    public $tituloModal = 'Nuevo Rol';

    public function render()
    {
        // Traemos todos los roles
        $roles = Role::orderBy('id', 'asc')->paginate(10);
        return view('livewire.admin.roles', compact('roles'));
    }

    public function crear()
    {
        $this->resetCampos();
        $this->tituloModal = 'Nuevo Rol';
        $this->dispatch('abrir-modal', id: 'modalRol');
    }

    public function guardar()
    {
        $this->validate([
            'name' => 'required|min:2|unique:roles,name,' . $this->role_id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        // Evitar que le cambien el nombre al super-admin
        if ($this->role_id) {
            $rol = Role::find($this->role_id);
            if ($rol->name === 'super-admin' && $this->name !== 'super-admin') {
                $this->dispatch('mostrar-toast', mensaje: 'No puedes cambiar el nombre del Super Admin.');
                return;
            }
        }

        Role::updateOrCreate(
            ['id' => $this->role_id],
            [
                'name' => strtolower(str_replace(' ', '-', $this->name)), // Formateamos el nombre como slug
                'guard_name' => 'web',
                'descripcion' => $this->descripcion
            ]
        );

        $this->dispatch('cerrar-modal', id: 'modalRol');
        $this->dispatch('mostrar-toast', mensaje: $this->role_id ? 'Rol actualizado exitosamente.' : 'Rol creado exitosamente.');
        
        $this->resetCampos();
    }

    public function editar($id)
    {
        $this->resetValidation();
        $rol = Role::findOrFail($id);
        $this->role_id = $rol->id;
        $this->name = $rol->name;
        $this->descripcion = $rol->descripcion;
        
        $this->tituloModal = 'Editar Rol';
        $this->dispatch('abrir-modal', id: 'modalRol');
    }

    public function eliminar($id)
    {
        $rol = Role::findOrFail($id);
        
        // Protección crítica
        if ($rol->name === 'super-admin') {
            $this->dispatch('mostrar-toast', mensaje: 'El rol Super Admin no puede ser eliminado.');
            return;
        }

        $rol->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Rol eliminado exitosamente.');
    }

    public function resetCampos()
    {
        $this->reset(['role_id', 'name', 'descripcion']);
        $this->resetValidation();
    }
}