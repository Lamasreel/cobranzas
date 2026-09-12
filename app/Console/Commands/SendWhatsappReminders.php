<?php

namespace App\Console\Commands;

use App\Services\WhatsappSender;
use App\Support\WhatsappAutomationSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendWhatsappReminders extends Command
{
    protected $signature = 'whatsapp:send-reminders {--force : Ejecutar aunque no sea el día configurado o esté deshabilitado} {--dry-run : No enviar, solo listar}';

    protected $description = 'Envía recordatorios por WhatsApp según reglas/fecha configuradas.';

    public function handle(WhatsappSender $whatsapp): int
    {
        $settings = WhatsappAutomationSettings::read();

        $enabled = (bool) ($settings['enabled'] ?? false);
        $force = (bool) $this->option('force');
        $dry = (bool) $this->option('dry-run');

        if (!$enabled && !$force) {
            return self::SUCCESS;
        }

        $dayOfMonth = (int) ($settings['day_of_month'] ?? 1);
        if ($dayOfMonth < 1 || $dayOfMonth > 31) {
            $this->error('Día inválido en configuración (debe ser 1-31).');
            return self::FAILURE;
        }

        $time = (string) ($settings['time'] ?? '09:00');
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $this->error('Hora inválida en configuración (usá HH:MM, ej: 09:30).');
            return self::FAILURE;
        }

        $now = Carbon::now();
        if (!$force) {
            if ((int) $now->day !== $dayOfMonth) {
                if ($this->output->isVerbose()) {
                    $this->line("Skip: hoy es día {$now->day}, configurado día {$dayOfMonth}.");
                }
                return self::SUCCESS;
            }

            if ($now->format('H:i') !== $time) {
                if ($this->output->isVerbose()) {
                    $this->line("Skip: ahora es {$now->format('H:i')}, configurado {$time}.");
                }
                return self::SUCCESS;
            }
        }

        $template = (string) config('services.whatsapp.auto_template');
        $lang = (string) config('services.whatsapp.auto_template_lang');

        if ($template === '' || $lang === '') {
            $this->error('Falta template/lang automático (services.whatsapp.auto_template / auto_template_lang).');
            return self::FAILURE;
        }

        $componentsJson = (string) config('services.whatsapp.auto_template_components_json', '');
        $components = null;
        if (trim($componentsJson) !== '') {
            $decoded = json_decode($componentsJson, true);
            if (!is_array($decoded)) {
                $this->error('WHATSAPP_AUTO_TEMPLATE_COMPONENTS_JSON no es JSON válido.');
                return self::FAILURE;
            }
            $components = $decoded;
        }

        // Promesas pendientes (sin resultado CUMPLIDO/INCUMPLIDO) cuya fecha
        // prometida es hoy, leídas de la tabla promesas_pago de sqlpremier.
        $promesas = DB::connection('mysql_local')
            ->table('promesas_pago')
            ->whereDate('fecha_prometida', $now->toDateString())
            ->whereNull('resultado')
            ->get(['dni', 'fecha_agendada', 'fecha_prometida', 'observaciones']);

        $this->info('Promesas con vencimiento hoy: ' . $promesas->count());

        if ($promesas->isEmpty()) {
            return self::SUCCESS;
        }

        // Datos de contacto de los morosos que tienen promesa vencida hoy.
        $dnis = $promesas->pluck('dni')->filter()->unique()->values()->all();
        $placeholders = implode(',', array_fill(0, count($dnis), '?'));

        $morosos = DB::connection('mysql_local')->select(
            "SELECT DNI, NOMBRE, TEL_MOVIL1, TEL_MOVIL2, TEL_MOVIL3, TEL_ALTER1, TEL_ALTER2
             FROM morosos
             WHERE DNI IN ($placeholders)",
            $dnis
        );

        $morososPorDni = collect($morosos)->keyBy(fn ($m) => trim((string) $m->DNI));

        $ok = 0;
        $fail = 0;

        foreach ($promesas as $promesa) {
            $moroso = $morososPorDni->get(trim((string) $promesa->dni));

            if (!$moroso) {
                $this->warn("Promesa del DNI {$promesa->dni} sin registro en morosos.");
                continue;
            }

            $phones = $this->collectPhones($moroso);

            if ($phones === []) {
                $this->warn("DNI {$moroso->DNI} sin teléfonos.");
                continue;
            }

            foreach ($phones as $phone) {
                $this->line("- {$moroso->DNI} {$moroso->NOMBRE} -> {$phone}");

                if ($dry) {
                    continue;
                }

                $resp = $whatsapp->sendTemplate($phone, $template, $lang, $components);
                if ($resp['ok']) {
                    $ok++;
                } else {
                    $fail++;
                    $this->error('Falló envío: ' . json_encode($resp, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        if (!$dry) {
            $this->info("Enviados OK: {$ok} | Fallidos: {$fail}");
        }

        return self::SUCCESS;
    }

    /**
     * Reúne los teléfonos de una fila de la tabla morosos
     * (TEL_MOVIL1..3, TEL_ALTER1..2).
     *
     * @return list<string>
     */
    private function collectPhones(object $m): array
    {
        $sources = [
            (string) ($m->TEL_MOVIL1 ?? ''),
            (string) ($m->TEL_MOVIL2 ?? ''),
            (string) ($m->TEL_MOVIL3 ?? ''),
            (string) ($m->TEL_ALTER1 ?? ''),
            (string) ($m->TEL_ALTER2 ?? ''),
        ];

        $phones = [];
        foreach ($sources as $src) {
            if (trim($src) === '') {
                continue;
            }
            foreach (preg_split('/[,\;\|\/]+/', $src) as $p) {
                $p = trim($p);
                if ($p === '') {
                    continue;
                }
                $digits = preg_replace('/\D+/', '', $p) ?? '';
                if ($digits === '') {
                    continue;
                }
                $phones[] = $digits;
            }
        }

        return array_values(array_unique($phones));
    }
}
