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
        $sql = "
            SELECT *
            FROM morosos
            WHERE DIAS BETWEEN ? AND ?
              AND {$campoFecha} IS NULL
              AND ORDEN = 1
              AND (
                    COALESCE(TEL_MOVIL1, '') <> ''
                 OR COALESCE(TEL_MOVIL2, '') <> ''
                 OR COALESCE(TEL_MOVIL3, '') <> ''
                 OR COALESCE(TEL_ALTER1, '') <> ''
                 OR COALESCE(TEL_ALTER2, '') <> ''
              )
            LIMIT 1
        ";
    
        $clientes = collect(DB::select($sql, [$desde, $hasta]));
    
        $this->info("Template {$template}: {$clientes->count()} clientes encontrados.");
    
        foreach ($clientes as $cliente) {
            $telefono = $this->obtenerTelefonoCliente($cliente);
    
            if (!$telefono) {
                $this->warn("Cliente DNI {$cliente->DNI} sin teléfono válido.");
                continue;
            }
    
            $response = $this->enviarTemplate($telefono, $template, $cliente);
    
            if ($response['status'] >= 200 && $response['status'] < 300) {
                DB::update("
                    UPDATE morosos
                    SET {$campoFecha} = NOW(),
                        ultimo_wsp_at = NOW(),
                        WSP = 'SI',
                        TIENE_WSP = 'SI'
                    WHERE ORDEN = ?
                      AND DNI = ?
                ", [
                    $cliente->ORDEN,
                    $cliente->DNI,
                ]);
            } else {
                $this->error("No se marcó como enviado el DNI {$cliente->DNI}. Meta respondió {$response['status']}");
            }
    
            Log::info('Aviso automático WhatsApp', [
                'orden' => $cliente->ORDEN,
                'dni' => $cliente->DNI,
                'telefono' => $telefono,
                'template' => $template,
                'status' => $response['status'],
                'body' => $response['body'],
            ]);
    
            $this->info("Cliente DNI {$cliente->DNI} enviado. Status: {$response['status']}");
        }
    }

    private function enviarTemplate(string $telefono, string $template, $cliente): array
    {
        $version = config('services.whatsapp.version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');

        $nombre = $cliente->NOMBRE ?? 'cliente';
        $fecha = now()->format('d/m/Y');
        $deuda = number_format($cliente->SAL_TOT ?? 0, 0, ',', '.');
        $dias = (string)($cliente->DIAS ?? 0);
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

    private function obtenerTelefonoCliente($cliente): ?string
{
    $telefonos = [
        $cliente->TEL_MOVIL1 ?? null,
        $cliente->TEL_MOVIL2 ?? null,
        $cliente->TEL_MOVIL3 ?? null,
        $cliente->TEL_ALTER1 ?? null,
        $cliente->TEL_ALTER2 ?? null,
    ];

    foreach ($telefonos as $telefono) {
        $normalizado = $this->normalizarTelefono($telefono);

        if ($normalizado) {
            return $normalizado;
        }
    }

    return null;
}
}