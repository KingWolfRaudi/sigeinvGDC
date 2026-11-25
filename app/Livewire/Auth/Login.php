<?php

namespace App\Livewire\Auth;

use App\Support\LoginThrottle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

// use Livewire\Attributes\Layout;

// Le indicamos que use nuestra plantilla base de Bootstrap
// #[Layout('layouts.app')]
class Login extends Component
{
    // Variables ligadas al formulario
    public $identificador = ''; // Puede ser email o username

    public $password = '';

    // Reglas de validación básicas (allowlist de tipo y longitud — gate 11)
    protected $rules = [
        'identificador' => 'required|string|max:255',
        'password' => 'required|string|max:255',
    ];

    public function iniciarSesion()
    {
        $this->validate();

        // CA-6 / CWE-307: límite de intentos fallidos por identificador+IP.
        // Misma regla que el limitador nombrado RateLimiter::for('login').
        $throttleKey = LoginThrottle::key((string) $this->identificador, (string) request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, LoginThrottle::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            abort(429, "Demasiados intentos de acceso. Intente de nuevo en {$seconds} segundos.");
        }

        // 1. Detección automática: ¿Es un correo o un nombre de usuario?
        // Usamos filter_var de PHP para ver si tiene un formato de email válido
        $tipoDeCampo = filter_var($this->identificador, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2. Armar las credenciales
        // NOTA: Agregamos 'activo' => 1 para asegurar que usuarios dados de baja no entren
        $credenciales = [
            $tipoDeCampo => $this->identificador,
            'password' => $this->password,
            'activo' => 1,
        ];

        // 3. Intentar autenticar
        if (Auth::attempt($credenciales, false)) {
            // Login correcto: restablecer el presupuesto de intentos
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            // Registrar auditoría de inicio de sesión
            activity()
                ->causedBy($user)
                ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('Inicio de sesión exitoso');

            // Por seguridad, regeneramos la sesión para evitar ataques de fijación
            session()->regenerate();

            // Marcador crítico para vinculación de sesión a la pestaña actual
            session()->flash('just_logged_in', true);

            // Redirigimos al inicio (que pronto será nuestro Dashboard)
            return redirect()->intended('/');
        }

        // 4. Si falla, contabilizar el intento, mostramos un error y registramos el intento fallido
        RateLimiter::hit($throttleKey, LoginThrottle::DECAY_SECONDS);

        activity()
            ->withProperties([
                'identificador_intentado' => $this->identificador,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Intento fallido de inicio de sesión');

        $this->addError('identificador', 'Las credenciales no coinciden o tu cuenta está inactiva.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
