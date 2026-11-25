<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Retención de logs de auditoría — R6 / gate 19 (privacidad).
 *
 * activity_log guarda IP y user-agent: se purga a los 12 meses mediante el
 * comando `activitylog:clean` programado en app/Console/Kernel.php.
 */
class ActivityRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_activity_records_are_pruned_and_recent_ones_kept(): void
    {
        activity()->log('registro reciente');
        activity()->log('registro antiguo');

        $reciente = Activity::query()->where('description', 'registro reciente')->firstOrFail();
        $antiguo = Activity::query()->where('description', 'registro antiguo')->firstOrFail();

        // Backdate manual (el modelo usa timestamps automáticos)
        Activity::query()->where('id', $antiguo->id)->update(['created_at' => now()->subDays(400)]);

        $this->artisan('activitylog:clean', ['--days' => 365, '--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('activity_log', ['id' => $antiguo->id]);
        $this->assertDatabaseHas('activity_log', ['id' => $reciente->id]);
    }
}
