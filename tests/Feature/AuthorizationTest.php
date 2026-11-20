<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de autorización negativos — CA-9 / matriz de permisos (F1 §5).
 *
 * Cada test demuestra que un rol SIN permiso es rechazado en el servidor
 * (CWE-862), independientemente de lo que muestre el menú.
 */
class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * T-AUTH-1 — CWE-306: sin credencial no hay contenido.
     */
    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * T-AUTH-2 — CWE-862: trabajador sin ver-auditoria-tecnicos → 403.
     */
    public function test_trabajador_cannot_access_auditoria_tecnicos(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->get('/admin/auditoria-tecnicos')
            ->assertStatus(403);
    }

    /**
     * T-AUTH-3 — CWE-862: trabajador sin ver-usuarios → 403.
     */
    public function test_trabajador_cannot_access_admin_usuarios(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->get('/admin/usuarios')
            ->assertStatus(403);
    }

    /**
     * T-AUTH-4 — CWE-862: trabajador sin reportes-pdf → 403 en ficha PDF.
     */
    public function test_trabajador_cannot_generate_ficha_pdf(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->get('/reportes/computador/1/ficha')
            ->assertStatus(403);
    }

    /**
     * T-AUTH-5 — CWE-862: personal-ti sin admin-auditoria → 403 en logs.
     */
    public function test_personal_ti_cannot_access_auditoria_logs(): void
    {
        $this->actingAs($this->userWithRole('personal-ti'))
            ->get('/admin/auditoria')
            ->assertStatus(403);
    }

    /**
     * T-AUTH-6 — CWE-862: trabajador sin reportes-masivos-filtros → 403.
     */
    public function test_trabajador_cannot_run_massive_report(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->post('/reportes/masivo/excel')
            ->assertStatus(403);
    }

    /**
     * T-AUTH-7 — CWE-862: /seed-permissions eliminada → 404 (no existe).
     */
    public function test_seed_permissions_route_was_removed(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->get('/seed-permissions')
            ->assertStatus(404);
    }

    /**
     * Control positivo: el rol CON permiso sí pasa (evita falsos positivos
     * por rutas rotas — si esto falla, el 403 de arriba no prueba nada).
     */
    public function test_trabajador_with_permissions_can_access_allowed_routes(): void
    {
        $this->actingAs($this->userWithRole('trabajador'))
            ->get('/perfil')
            ->assertStatus(200);
    }

    /**
     * Control positivo de la T-AUTH-5: administrador (con admin-auditoria)
     * sí accede a /admin/auditoria.
     */
    public function test_administrador_can_access_auditoria_logs(): void
    {
        $this->actingAs($this->userWithRole('administrador'))
            ->get('/admin/auditoria')
            ->assertStatus(200);
    }
}
