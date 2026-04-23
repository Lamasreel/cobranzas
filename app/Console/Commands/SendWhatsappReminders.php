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
        if (!preg_match('/^\\d{2}:\\d{2}$/', $time)) {
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

        $estadoPromesa = (int) config('services.whatsapp.auto_estado_promesa_id');
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

        $query = DB::table('cliente')
            ->where('estado', $estadoPromesa)
            ->whereDate('fecha_promesa_pago', $now->toDateString());

        $clientes = $query->get();

        $this->info('Clientes elegibles: ' . $clientes->count());

        $ok = 0;
        $fail = 0;

        foreach ($clientes as $c) {
            $phones = $this->collectPhones($c);

            if ($phones === []) {
                $this->warn("Cliente {$c->id} sin teléfonos.");
                continue;
            }

            foreach ($phones as $phone) {
                $this->line("- {$c->id} {$c->nombre} -> {$phone}");

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
     * @return list<string>
     */
    private function collectPhones(object $c): array
    {
        $sources = [
            (string) ($c->telefono ?? ''),
            (string) ($c->telefono_1 ?? ''),
            (string) ($c->telefono_2 ?? ''),
            (string) ($c->telefono_3 ?? ''),
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
