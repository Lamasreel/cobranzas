<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviarAvisosMoraWhatsapp extends Command
{
    protected $signature = 'app:enviar-avisos-mora-whatsapp';

    protected $description = 'Envía avisos automáticos de mora por WhatsApp';

    public function handle()
    {
        $this->info('Iniciando envío automático de avisos...');

        $this->enviarPorRango(30, 60, 'primer_aviso_mora', 'wsp_primer_aviso_at');
        $this->enviarPorRango(60, 90, 'segundo_aviso_mora', 'wsp_segundo_aviso_at');
        $this->enviarPorRango(90, 120, 'aviso_prejudicial_mora', 'wsp_prejudicial_at');

        $this->info('Proceso finalizado.');

        return self::SUCCESS;
    }

    private function enviarPorRango(int $desde, int $hasta, string $template, string $campoFecha): void
    {
        $clientes = DB::table('maectas2')
            ->whereBetween('DIAS', [$desde, $hasta])
            ->whereNotNull('TEL_MOVIL')
            ->limit(1) // prueba segura
            ->get();

        $this->info("Template {$template}: {$clientes->count()} clientes encontrados.");

        foreach ($clientes as $cliente) {
            $telefono = $this->normalizarTelefono($cliente->TEL_MOVIL);

            if (!$telefono) {
                $this->warn("Cliente {$cliente->id} sin teléfono válido.");
                continue;
            }

            $response = $this->enviarTemplate($telefono, $template, $cliente);

            if ($response['status'] >= 200 && $response['status'] < 300) {
                DB::table('cliente')
                    ->where('id', $cliente->id)
                    ->update([
                        $campoFecha => now(),
                        'ultimo_wsp_at' => now(),
                    ]);
            } else {
                $this->error("No se marcó como enviado el cliente {$cliente->id} porque Meta respondió {$response['status']}");
            }

            Log::info('Aviso automático WhatsApp', [
                'cliente_id' => $cliente->id,
                'documento' => $cliente->documento ?? null,
                'telefono' => $telefono,
                'template' => $template,
                'status' => $response['status'],
                'body' => $response['body'],
            ]);

            $this->info("Cliente {$cliente->id} enviado. Status: {$response['status']}");
        }
    }

    private function enviarTemplate(string $telefono, string $template, $cliente): array
    {
        $version = config('services.whatsapp.version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');

        $nombre = $cliente->nombre ?? 'cliente';
        $fecha = now()->format('d/m/Y');
        $deuda = number_format($cliente->saldo_total ?? 0, 0, ',', '.');
        $dias = (string)($cliente->dias ?? 0);

        $parameters = [];

        if ($template === 'primer_aviso_mora') {
            $parameters = [
                ['type' => 'text', 'text' => $nombre],
                ['type' => 'text', 'text' => $fecha],
                ['type' => 'text', 'text' => $deuda],
            ];
        }

        if ($template === 'segundo_aviso_mora') {
            $parameters = [
                ['type' => 'text', 'text' => $nombre],
                ['type' => 'text', 'text' => $deuda],
                ['type' => 'text', 'text' => '96'],
            ];
        }

        if ($template === 'aviso_prejudicial_mora') {
            $parameters = [
                ['type' => 'text', 'text' => $nombre],
                ['type' => 'text', 'text' => $dias],
                ['type' => 'text', 'text' => now()->addDays(3)->format('d/m/Y')],
            ];
        }

        $idioma = '';

        if ($template === 'segundo_aviso_mora'){
            $idioma = 'en';
        } else {
            $idioma = 'es_AR';
        }


        $response = Http::withToken($token)->post(
            "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => $template,
                    'language' => [
                        'code' => $idioma,
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => $parameters,
                        ],
                    ],
                ],
            ]
        );

        return [
            'status' => $response->status(),
            'body' => $response->json(),
        ];
    }

    private function normalizarTelefono(?string $telefono): ?string
    {
        if (!$telefono) {
            return null;
        }

        $telefono = preg_replace('/\D/', '', $telefono);

        if (!$telefono) {
            return null;
        }

        if (str_starts_with($telefono, '0')) {
            $telefono = substr($telefono, 1);
        }

        if (!str_starts_with($telefono, '54')) {
            $telefono = '54' . $telefono;
        }

        return $telefono;
    }
}