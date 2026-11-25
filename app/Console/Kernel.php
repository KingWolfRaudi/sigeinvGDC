<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Retención de logs de auditoría (R6 / GDPR-ish): purga de activity_log
        // con más de 12 meses (contiene IP + user-agent). `--force` porque el
        // scheduler corre con APP_ENV=production.
        $schedule->command('activitylog:clean', ['--days' => 365, '--force' => true])->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
