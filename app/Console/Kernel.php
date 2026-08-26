<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Mantiene al día la tabla resumen de pagos de jlv_parte (provisión/red de seguridad).
        $schedule->command('app:actualizar-pagos-resumen')
            ->dailyAt('04:30');

        $schedule->command('app:enviar-avisos-mora')
        ->monthlyOn(23, '20:26');
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
