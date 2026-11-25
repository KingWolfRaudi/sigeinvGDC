<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Validación de entrada del login — gate 11 / CWE-20.
 *
 * La UI valida, pero el servidor revalida: tipo, longitud y entrada
 * malformada no deben llegar a la consulta de autenticación.
 */
class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_both_fields(): void
    {
        Livewire::test(Login::class)
            ->set('identificador', '')
            ->set('password', '')
            ->call('iniciarSesion')
            ->assertHasErrors(['identificador' => 'required', 'password' => 'required']);
    }

    public function test_login_rejects_overlong_identificador(): void
    {
        Livewire::test(Login::class)
            ->set('identificador', str_repeat('a', 256))
            ->set('password', 'irrelevante')
            ->call('iniciarSesion')
            ->assertHasErrors(['identificador' => 'max']);
    }

    public function test_login_handles_sql_metacharacters_safely(): void
    {
        // Entrada tipo inyección: debe fallar como credencial inválida,
        // nunca producir un 500 ni autenticar.
        Livewire::test(Login::class)
            ->set('identificador', "' OR 1=1 --")
            ->set('password', "' OR '1'='1")
            ->call('iniciarSesion')
            ->assertHasErrors('identificador');

        $this->assertGuest();
    }
}
