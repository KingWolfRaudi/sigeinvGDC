<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class Usuarios extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Variables de búsqueda y filtros
    public $search = '';
    public $sortField = 'id';
    public $sortAsc = false;
    public $filtro_estado = 'todos';

    // Variables del formulario
    public $user_id, $name, $username, $email, $password;
    public bool $activo = true;
    public $roles_seleccionados = []; 
    public $tituloModal = 'Nuevo Usuario';
    
    // Variable para el modal de detalle
    public $usuario_detalle;

    // Resetear paginación al buscar
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Método para ordenar columnas
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
            $this->sortField = $field;
        }
    }

    public function render()
    {
        // 1. Iniciamos la consulta excluyendo al super-admin
        $query = User::with('roles')->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super-admin');
        });

        // 2. Lógica de Estados y Visibilidad
        if (Gate::allows('ver-estado-usuarios')) {
            if ($this->filtro_estado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtro_estado === 'inactivos') {
                $query->where('activo', false);
            }
        } else {
            // Sin permiso, solo ve activos
            $query->where('activo', true);
        }

        // 3. Búsqueda
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('username', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%');
        });

        $usuarios = $query->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
                          ->paginate(10);
        
        // 4. Cargamos roles excluyendo el super-admin para que no pueda ser asignado
        $roles = Role::where('name', '!=', 'super-admin')->orderBy('name', 'asc')->get();
        
        return view('livewire.admin.usuarios', compact('usuarios', 'roles'));
    }

    public function ver($id)
    {
        abort_if(Gate::denies('ver-usuarios'), 403);
        
        $this->usuario_detalle = User::with('roles')->findOrFail($id);
        
        // Medida de seguridad adicional: No permitir ver detalle del super-admin si logran inyectar el ID
        if ($this->usuario_detalle->hasRole('super-admin')) {
            abort(403, 'Acceso denegado al superusuario.');
        }

        $this->dispatch('abrir-modal', id: 'modalDetalleUsuario');
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-usuarios'), 403);

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Usuario';
        $this->dispatch('abrir-modal', id: 'modalUsuario');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->user_id ? 'editar-usuarios' : 'crear-usuarios'), 403);
        
        $reglas = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user_id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->user_id,
            'password' => $this->user_id ? 'nullable|min:8' : 'required|min:8',
        ];

        $this->validate($reglas);

        $datos = [
            'name' => $this->name,
            'username' => strtolower($this->username),
            'email' => strtolower($this->email),
            'activo' => $this->activo ? 1 : 0
        ];

        if (!empty($this->password)) {
            $datos['password'] = Hash::make($this->password);
        }

        $usuario = User::updateOrCreate(['id' => $this->user_id], $datos);

        // Prevenir que mediante inyección en el frontend intenten asignarse 'super-admin'
        $rolesValidos = Role::whereIn('name', $this->roles_seleccionados)
                            ->where('name', '!=', 'super-admin')
                            ->pluck('name')
                            ->toArray();

        $usuario->syncRoles($rolesValidos);

        $this->dispatch('cerrar-modal', id: 'modalUsuario');
        $this->dispatch('mostrar-toast', mensaje: $this->user_id ? 'Usuario actualizado exitosamente.' : 'Usuario creado exitosamente.', tipo: 'success');
        
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-usuarios'), 403);
        $this->resetValidation();
        
        $usuario = User::findOrFail($id);
        
        if ($usuario->hasRole('super-admin')) {
            abort(403, 'No se puede editar al superusuario.');
        }
        
        $this->user_id = $usuario->id;
        $this->name = $usuario->name;
        $this->username = $usuario->username;
        $this->email = $usuario->email;
        $this->activo = (bool) $usuario->activo;
        $this->password = ''; 
        
        $this->roles_seleccionados = $usuario->roles->pluck('name')->toArray();
        
        $this->tituloModal = 'Editar Usuario';
        $this->dispatch('abrir-modal', id: 'modalUsuario');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-usuarios'), 403); 
        
        if (Auth::id() == $id) {
            $this->dispatch('mostrar-toast', mensaje: 'No puedes desactivar tu propia cuenta.', tipo: 'warning');
            return;
        }

        $usuario = User::findOrFail($id);

        if ($usuario->hasRole('super-admin')) {
            $this->dispatch('mostrar-toast', mensaje: 'El superusuario no puede ser desactivado.', tipo: 'error');
            return;
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        $estado = $usuario->activo ? 'activado' : 'desactivado';
        $this->dispatch('mostrar-toast', mensaje: "Usuario {$estado} exitosamente.", tipo: 'success');
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-usuarios'), 403);
        
        if (Auth::id() == $id) {
            $this->dispatch('mostrar-toast', mensaje: 'No puedes eliminar tu propia cuenta.', tipo: 'warning');
            return;
        }

        $usuario = User::findOrFail($id);

        if ($usuario->hasRole('super-admin')) {
            $this->dispatch('mostrar-toast', mensaje: 'El superusuario no puede ser eliminado.', tipo: 'error');
            return;
        }

        $usuario->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Usuario eliminado exitosamente.', tipo: 'success');
    }

    public function resetCampos()
    {
        $this->reset(['user_id', 'name', 'username', 'email', 'password', 'roles_seleccionados', 'usuario_detalle']);
        $this->activo = true;
        $this->resetValidation();
    }
}