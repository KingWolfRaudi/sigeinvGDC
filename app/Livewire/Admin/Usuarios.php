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

    public $user_id, $name, $username, $email, $password;
    public bool $activo = true;
    public $roles_seleccionados = []; // Arreglo para los roles
    public $tituloModal = 'Nuevo Usuario';

    public function render()
    {
        $usuarios = User::orderBy('id', 'desc')->paginate(10);
        $roles = Role::orderBy('name', 'asc')->get();
        
        return view('livewire.admin.usuarios', compact('usuarios', 'roles'));
    }

    public function crear()
    {
        abort_if(Gate::denies('crear-usuarios'), 403); // PROTECCIÓN

        $this->resetCampos();
        $this->tituloModal = 'Nuevo Usuario';
        $this->dispatch('abrir-modal', id: 'modalUsuario');
    }

    public function guardar()
    {
        abort_if(Gate::denies($this->user_id ? 'editar-usuarios' : 'crear-usuarios'), 403);
        // Reglas de validación dinámicas (la contraseña es obligatoria solo si es usuario nuevo)
        $reglas = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user_id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->user_id,
            'password' => $this->user_id ? 'nullable|min:8' : 'required|min:8',
        ];

        $this->validate($reglas);

        // Preparamos los datos base
        $datos = [
            'name' => $this->name,
            'username' => strtolower($this->username),
            'email' => strtolower($this->email),
            'activo' => $this->activo ? 1 : 0
        ];

        // Si escribió una contraseña, la encriptamos y la agregamos a los datos
        if (!empty($this->password)) {
            $datos['password'] = Hash::make($this->password);
        }

        // Creamos o actualizamos
        $usuario = User::updateOrCreate(['id' => $this->user_id], $datos);

        // Sincronizamos los roles usando Spatie
        $usuario->syncRoles($this->roles_seleccionados);

        $this->dispatch('cerrar-modal', id: 'modalUsuario');
        $this->dispatch('mostrar-toast', mensaje: $this->user_id ? 'Usuario actualizado exitosamente.' : 'Usuario creado exitosamente.');
        
        $this->resetCampos();
    }

    public function editar($id)
    {
        abort_if(Gate::denies('editar-usuarios'), 403);
        $this->resetValidation();
        $usuario = User::findOrFail($id);
        
        $this->user_id = $usuario->id;
        $this->name = $usuario->name;
        $this->username = $usuario->username;
        $this->email = $usuario->email;
        $this->activo = (bool) $usuario->activo;
        $this->password = ''; // Dejamos la contraseña en blanco por seguridad
        
        // Extraemos los roles actuales del usuario
        $this->roles_seleccionados = $usuario->roles->pluck('name')->toArray();
        
        $this->tituloModal = 'Editar Usuario';
        $this->dispatch('abrir-modal', id: 'modalUsuario');
    }

    public function toggleActivo($id)
    {
        abort_if(Gate::denies('cambiar-estatus-usuarios'), 403); // PROTECCIÓN
        if (Auth::id() == $id) {
            $this->dispatch('mostrar-toast', mensaje: 'No puedes desactivar tu propia cuenta.');
            return;
        }

        $usuario = User::findOrFail($id);
        $usuario->activo = !$usuario->activo;
        $usuario->save();

        $estado = $usuario->activo ? 'activado' : 'desactivado';
        $this->dispatch('mostrar-toast', mensaje: "Usuario {$estado} exitosamente.");
    }

    public function eliminar($id)
    {
        abort_if(Gate::denies('eliminar-usuarios'), 403);
        if (Auth::id() == $id) {
            $this->dispatch('mostrar-toast', mensaje: 'No puedes eliminar tu propia cuenta.');
            return;
        }

        User::findOrFail($id)->delete();
        $this->dispatch('mostrar-toast', mensaje: 'Usuario eliminado exitosamente.');
    }

    public function resetCampos()
    {
        $this->reset(['user_id', 'name', 'username', 'email', 'password', 'roles_seleccionados']);
        $this->activo = true;
        $this->resetValidation();
    }
}