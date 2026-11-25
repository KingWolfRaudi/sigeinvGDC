<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
// IMPORTANTE: Agrega esta línea arriba
use App\Support\LoginThrottle;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // CA-6 / CWE-307: límite de intentos de login. El componente
        // Livewire\Auth\Login aplica la misma regla vía LoginThrottle
        // (hoy el login es una acción Livewire, sin ruta propia; este
        // limitador nombrado queda listo para middleware throttle y usa
        // la misma clave compartida LoginThrottle::key).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(LoginThrottle::MAX_ATTEMPTS)->by(
                LoginThrottle::key(
                    (string) $request->input('identificador', ''),
                    (string) $request->ip()
                )
            );
        });

        // Vista de configuración: cualquiera de los tres permisos (equivale
        // a canAny en ruta — Route::canAny no existe en Laravel 13)
        Gate::define('admin.configuracion', function ($user) {
            return $user->can('admin-incidencias')
                || $user->can('admin-solicitudes-perfil')
                || $user->can('admin-configuracion-global');
        });

        // Magia de Spatie: El Super Admin tiene acceso a TODO automáticamente
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
