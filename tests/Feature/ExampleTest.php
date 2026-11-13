<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Un invitado que visita la raíz debe ser redirigido al login (deny by default).
     * CWE-306 — sin credencial no hay contenido.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * La página de login debe responder 200 a un invitado.
     */
    public function test_login_page_is_available_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
