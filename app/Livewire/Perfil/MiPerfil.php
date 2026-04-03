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

class MiPerfil extends Component
{
    use WithFileUploads;

    // Foto de Perfil
    public $nueva_foto;

    // Campos de Solicitud
    public $nuevo_nombre, $nuevo_username, $nuevo_email, $nuevo_password, $confirmar_password;

    // Configuración
    public $config = [];

    public function mount()
    {
        $this->loadConfig();
        $user = Auth::user();
        $this->nuevo_nombre = $user->name;
        $this->nuevo_username = $user->username;
        $this->nuevo_email = $user->email;
    }

    public function loadConfig()
    {
        $this->config = Configuracion::where('grupo', 'perfil')
            ->pluck('valor', 'clave')
            ->toArray();
    }

    public function updatedNuevaFoto()
    {
        $this->validate([
            'nueva_foto' => 'image|max:1024', // 1MB Max
        ]);

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
        // 1. Verificar si la opción está habilitada en config
        if (!($this->config['perfil_solicitar_' . $tipo] ?? false)) {
            $this->dispatch('mostrar-toast', mensaje: 'Esta solicitud está deshabilitada por el administrador.', tipo: 'danger');
            return;
        }

        // 2. Verificar la regla de los 180 días
        if (!SolicitudPerfil::canRequest(Auth::id(), $tipo)) {
            $días = SolicitudPerfil::daysRemaining(Auth::id(), $tipo);
            $this->dispatch('mostrar-toast', mensaje: "No puedes solicitar este cambio aún. Faltan $días días.", tipo: 'warning');
            return;
        }

        // 3. Validar datos según el tipo
        $valorStr = "nuevo_$tipo";
        $valor = $this->$valorStr;

        $rules = [];
        if ($tipo === 'nombre') $rules['nuevo_nombre'] = 'required|min:3';
        if ($tipo === 'username') $rules['nuevo_username'] = 'required|min:4|unique:users,username,' . Auth::id();
        if ($tipo === 'email') $rules['nuevo_email'] = 'required|email|unique:users,email,' . Auth::id();
        if ($tipo === 'password') {
            $this->validate([
                'nuevo_password' => 'required|min:8',
                'confirmar_password' => 'same:nuevo_password',
            ]);
            $valor = Hash::make($this->nuevo_password);
        } else {
            $this->validate($rules);
        }

        // 4. Crear solicitud
        SolicitudPerfil::create([
            'user_id' => Auth::id(),
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

    public function render()
    {
        return view('livewire.perfil.mi-perfil', [
            'misSolicitudes' => SolicitudPerfil::where('user_id', Auth::id())->latest()->take(5)->get(),
        ])->layout('components.layouts.app');
    }
}
