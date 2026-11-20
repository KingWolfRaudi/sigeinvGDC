<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Límite de intentos de login (CA-6 / CWE-307).
 *
 * Compartido por:
 *  - el limitador nombrado `login` registrado en AppServiceProvider
 *    (RateLimiter::for('login'), usable con middleware throttle en rutas),
 *  - el componente Livewire Auth\Login (único endpoint de login hoy).
 *
 * Clave: identificador (email/username normalizado) + IP — impide que un
 * atacante desde otra IP agote el presupuesto de una víctima, y viceversa.
 */
class LoginThrottle
{
    /** Intentos fallidos permitidos por ventana. */
    public const MAX_ATTEMPTS = 5;

    /** Duración de la ventana en segundos (1 minuto). */
    public const DECAY_SECONDS = 60;

    public static function key(string $identificador, string $ip): string
    {
        return 'login:'.Str::lower(trim($identificador)).'|'.$ip;
    }
}
