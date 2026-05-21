<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
//use Livewire\Attributes\Layout;

// Le indicamos que use nuestra plantilla base de Bootstrap
//#[Layout('layouts.app')]
class Login extends Component
{
    // Variables ligadas al formulario
    public $identificador = ''; // Puede ser email o username
    public $password = '';

    // Reglas de validación básicas
    protected $rules = [
        'identificador' => 'required',
        'password' => 'required'
    ];

    public function iniciarSesion()
    {
        $this->validate();

        // 1. Detección automática: ¿Es un correo o un nombre de usuario?
        // Usamos filter_var de PHP para ver si tiene un formato de email válido
        $tipoDeCampo = filter_var($this->identificador, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2. Armar las credenciales
        // NOTA: Agregamos 'activo' => 1 para asegurar que usuarios dados de baja no entren
        $credenciales = [
            $tipoDeCampo => $this->identificador,
            'password'   => $this->password,
            'activo'     => 1 
        ];

        // 3. Intentar autenticar
        if (Auth::attempt($credenciales, false)) {
            $user = Auth::user();
            
            // Registrar auditoría de inicio de sesión
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('Inicio de sesión exitoso');

            // Por seguridad, regeneramos la sesión para evitar ataques de fijación
            session()->regenerate();

            // Redirigimos al inicio (que pronto será nuestro Dashboard)
            return redirect()->intended('/');
        }

        // 4. Si falla, mostramos un error y registramos el intento fallido
        activity()
            ->withProperties([
                'identificador_intentado' => $this->identificador,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ])
            ->log('Intento fallido de inicio de sesión');

        $this->addError('identificador', 'Las credenciales no coinciden o tu cuenta está inactiva.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}