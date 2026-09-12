<?php

namespace App\Console;

use App\Support\WhatsappAutomationSettings;
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

        // Envío automático de avisos de mora por WhatsApp.
        // El día del mes y la hora se toman de la configuración guardada desde la UI
        // (morosos > WhatsApp automático), persistida en WhatsappAutomationSettings.
        $auto = WhatsappAutomationSettings::read();

        if (!empty($auto['enabled'])) {
            $day = (int) ($auto['day_of_month'] ?? 0);
            $time = (string) ($auto['time'] ?? '');

            if ($day >= 1 && $day <= 31 && preg_match('/^\d{2}:\d{2}$/', $time)) {
                $schedule->command('app:enviar-avisos-mora-whatsapp')
                    ->monthlyOn($day, $time)
                    ->withoutOverlapping();
            }
        }

        $schedule->command('whatsapp:send-reminders')
            ->everyMinute()
            ->withoutOverlapping();
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
