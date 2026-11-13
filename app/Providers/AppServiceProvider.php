<?php

namespace App\Providers;

// use Illuminate\Support\ServiceProvider;
// IMPORTANTE: Agrega esta línea arriba
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Magia de Spatie: El Super Admin tiene acceso a TODO automáticamente
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
