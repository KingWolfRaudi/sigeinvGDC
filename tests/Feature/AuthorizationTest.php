<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\MainDashboard;
use App\Livewire\Incidencias\Gestion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
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
     * Control positivo: el rol CON permiso sí pasa la autorización (la
     * validación del payload ocurre después, en el servidor).
     */
    public function test_massive_report_rejects_unknown_module(): void
    {
        $this->actingAs($this->userWithRole('administrador'))
            ->post('/reportes/masivo/excel', ['selections' => [['module' => 'no-existe']]])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /**
     * Payload malformado (módulo no string) → redirect con error, no 500.
     */
    public function test_massive_report_rejects_malformed_selections(): void
    {
        $this->actingAs($this->userWithRole('administrador'))
            ->post('/reportes/masivo/excel', ['selections' => [['module' => 123]]])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /**
     * UI ≠ servidor: el botón "Exportar" de gestión de incidencias solo se
     * muestra con reportes-excel (el resolutor no lo tiene → sin botón roto).
     */
    public function test_gestion_hides_export_button_without_permission(): void
    {
        $this->actingAs($this->userWithRole('resolutor-incidencia'))
            ->get('/incidencias/gestion')
            ->assertStatus(200)
            ->assertDontSee('Exportar');
    }

    /**
     * Control positivo de la UI: quien tiene reportes-excel sí ve el botón.
     */
    public function test_gestion_shows_export_button_with_permission(): void
    {
        $this->actingAs($this->userWithRole('personal-ti'))
            ->get('/incidencias/gestion')
            ->assertStatus(200)
            ->assertSee('Exportar');
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

    /**
     * CWE-639 (IDOR): `/` solo exige ver-dashboard, así que la acción Livewire
     * ver() debe acotar la incidencia a su propietario/gestor.
     */
    public function test_trabajador_cannot_open_another_users_incidence(): void
    {
        $dueno = $this->userWithRole('trabajador');
        $atacante = $this->userWithRole('trabajador');

        $departamentoId = DB::table('departamentos')->insertGetId(['nombre' => 'Depto IDOR']);
        $problemaId = DB::table('problemas')->insertGetId(['nombre' => 'Problema IDOR']);
        $incidenciaId = DB::table('incidencias')->insertGetId([
            'problema_id' => $problemaId,
            'departamento_id' => $departamentoId,
            'descripcion' => 'Incidencia de prueba para IDOR',
            'created_by' => $dueno->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Atacante: 403
        $this->actingAs($atacante);

        Livewire::test(MainDashboard::class)
            ->call('ver', $incidenciaId)
            ->assertStatus(403);

        // Control: el propietario sí puede abrirla (si esto falla, el 403 no prueba nada)
        $this->actingAs($dueno);

        Livewire::test(MainDashboard::class)
            ->call('ver', $incidenciaId)
            ->assertStatus(200);
    }

    /**
     * CWE-862: la acción Livewire crear() de la mesa de soporte revalida
     * crear-ticket en el servidor (no basta con ocultar el botón en la UI).
     */
    public function test_resolutor_cannot_open_create_incidence_form(): void
    {
        $this->actingAs($this->userWithRole('resolutor-incidencia'));

        Livewire::test(Gestion::class)
            ->call('crear')
            ->assertStatus(403);
    }

    /**
     * Control: personal-ti (con crear-ticket) sí puede abrir el formulario.
     */
    public function test_personal_ti_can_open_create_incidence_form(): void
    {
        $this->actingAs($this->userWithRole('personal-ti'));

        Livewire::test(Gestion::class)
            ->call('crear')
            ->assertStatus(200);
    }

    /**
     * CWE-862: persistir (guardar) una incidencia nueva también exige
     * crear-ticket en el servidor, no solo en la vista.
     */
    public function test_resolutor_cannot_persist_new_incidence(): void
    {
        $this->actingAs($this->userWithRole('resolutor-incidencia'));

        Livewire::test(Gestion::class)
            ->call('guardar')
            ->assertStatus(403);
    }
}
