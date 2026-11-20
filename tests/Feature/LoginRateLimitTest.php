<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Rate limit de login — CA-6 / CWE-307 (fuerza bruta).
 */
class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CA-6: el limitador nombrado RateLimiter::for('login') debe existir.
     */
    public function test_named_login_limiter_is_registered(): void
    {
        $this->assertNotNull(
            RateLimiter::limiter('login'),
            'Falta RateLimiter::for(\'login\') en AppServiceProvider'
        );
    }

    /**
     * T-AUTH-8 — tras 5 intentos fallidos, el 6º recibe 429.
     */
    public function test_login_returns_429_after_five_failed_attempts(): void
    {
        foreach (range(1, 5) as $intento) {
            Livewire::test(Login::class)
                ->set('identificador', 'victima@sigeinv.test')
                ->set('password', 'wrong-password-'.$intento)
                ->call('iniciarSesion')
                ->assertHasErrors('identificador');
        }

        // Livewire convierte el abort(429) en una respuesta (no relanza la
        // HttpException), y Testable::__call reenvía assertStatus al
        // TestResponse subyacente.
        Livewire::test(Login::class)
            ->set('identificador', 'victima@sigeinv.test')
            ->set('password', 'wrong-again')
            ->call('iniciarSesion')
            ->assertStatus(429);
    }

    /**
     * Control: otro identificador NO está bloqueado (la clave es por
     * identificador+IP, no global).
     */
    public function test_rate_limit_is_scoped_per_identifier(): void
    {
        foreach (range(1, 5) as $intento) {
            Livewire::test(Login::class)
                ->set('identificador', 'atacante@sigeinv.test')
                ->set('password', 'wrong-'.$intento)
                ->call('iniciarSesion')
                ->assertHasErrors('identificador');
        }

        // La víctima sigue pudiendo intentar (falla por credenciales, no por 429)
        Livewire::test(Login::class)
            ->set('identificador', 'victima@sigeinv.test')
            ->set('password', 'still-wrong')
            ->call('iniciarSesion')
            ->assertHasErrors('identificador');
    }
}
