<?php

namespace App\Livewire\Perfil;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\SolicitudPerfil;
use App\Models\Configuracion;
use Livewire\Attributes\Layout;

/**
 * MiPerfil Component
 */
class MiPerfil extends Component
{
    use WithFileUploads;

    // Foto de Perfil
    public $nueva_foto;

    // Campos de Solicitud
    public $nuevo_nombre, $nuevo_username, $nuevo_email, $nuevo_password, $confirmar_password;

    // Disponibilidad Técnica
    public $disponible_asignacion = false;
    public $es_tecnico = false;

    // Configuración
    public $config = [];

    public function mount()
    {
        $this->loadConfig();
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $this->nuevo_nombre = $user->name;
        $this->nuevo_username = $user->username;
        $this->nuevo_email = $user->email;
        
        $this->es_tecnico = $user->hasRole(['personal-ti', 'resolutor-incidencia']);
        $this->disponible_asignacion = (bool) $user->disponible_asignacion;
    }

    public function toggleDisponibilidad()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($this->es_tecnico) {
            $user->disponible_asignacion = $this->disponible_asignacion;
            $user->save();
            $this->dispatch('mostrar-toast', mensaje: $this->disponible_asignacion ? 'Ahora estás disponible para asignaciones.' : 'Ya no recibirás asignaciones automáticas.', tipo: 'success');
        }
    }

    public function loadConfig()
    {
        $this->config = Configuracion::where('grupo', 'perfil')
            ->pluck('valor', 'clave')
            ->toArray();
    }

    public function updatedNuevaFoto()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->hasRole('super-admin')) {
            $this->dispatch('mostrar-toast', mensaje: 'El Super Administrador es inmutable.', tipo: 'danger');
            $this->reset('nueva_foto');
            return;
        }

        $this->validate([
            'nueva_foto' => 'image|max:1024', // 1MB Max
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Generar nombre: nombre-completo-slug.ext
        $filename = Str::slug($user->name) . '.' . $this->nueva_foto->getClientOriginalExtension();

        // Si ya tenía una foto con el mismo nombre o diferente, la borramos si existe (opcional, pero limpio)
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $this->nueva_foto->storeAs('avatars', $filename, 'public');

        $user->avatar = $path;
        $user->save();

        $this->dispatch('mostrar-toast', mensaje: 'Foto de perfil actualizada.', tipo: 'success');
        $this->reset('nueva_foto');
    }

    public function enviarSolicitud($tipo)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($user->hasRole('super-admin')) {
            $this->dispatch('mostrar-toast', mensaje: 'El perfil del Super Administrador no puede ser modificado.', tipo: 'danger');
            return;
        }

        // 1. Verificar si la opción está habilitada en config
        if (!($this->config['perfil_solicitar_' . $tipo] ?? false)) {
            $this->dispatch('mostrar-toast', mensaje: 'Esta solicitud está deshabilitada por el administrador.', tipo: 'danger');
            return;
        }

        // 2. Verificar la regla de los 180 días
        if (!SolicitudPerfil::canRequest($user->id, $tipo)) {
            $días = SolicitudPerfil::daysRemaining($user->id, $tipo);
            $this->dispatch('mostrar-toast', mensaje: "No puedes solicitar este cambio aún. Faltan $días días.", tipo: 'warning');
            return;
        }

        // 3. Validar datos según el tipo
        $valorStr = "nuevo_$tipo";
        $valor = $this->$valorStr;

        $rules = [];
        if ($tipo === 'nombre') $rules['nuevo_nombre'] = 'required|min:3';
        if ($tipo === 'username') $rules['nuevo_username'] = 'required|min:4|unique:users,username,' . $user->id;
        if ($tipo === 'email') $rules['nuevo_email'] = 'required|email|unique:users,email,' . $user->id;
        if ($tipo === 'password') {
            $this->validate([
                'nuevo_password' => 'required|min:8',
                'confirmar_password' => 'same:nuevo_password',
            ]);
            $valor = Hash::make($this->nuevo_password);
        } else {
            $this->validate($rules);
        }

        // 4. Crear solicitud (Flujo Normal)
        SolicitudPerfil::create([
            'user_id' => $user->id,
            'tipo' => $tipo,
            'valor_nuevo' => $valor,
            'estado' => 'pendiente',
        ]);

        $this->dispatch('mostrar-toast', mensaje: 'Solicitud enviada con éxito. Pendiente de aprobación.', tipo: 'success');
        
        if ($tipo === 'password') {
            $this->reset(['nuevo_password', 'confirmar_password']);
        }
    }

    public function cancelarSolicitud($id)
    {
        $sol = SolicitudPerfil::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('estado', 'pendiente')
            ->firstOrFail();

        $sol->update(['estado' => 'cancelado']);
        $this->dispatch('mostrar-toast', mensaje: 'Solicitud cancelada por el usuario.', tipo: 'info');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.perfil.mi-perfil', [
            'misSolicitudes' => SolicitudPerfil::where('user_id', Auth::id())->latest()->take(5)->get(),
        ]);
    }
}
