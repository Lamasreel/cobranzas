<?php

namespace App\Http\Controllers;

use App\Models\WhatsappConversacion;
use App\Models\WhatsappMensaje;
use App\Support\PromesaDePago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappBotController extends Controller
{
    private string $connection = 'mysql_local';
    private string $tabla = 'morosos';

    public function verificar(Request $request)
    {
        $mode = $request->get('hub_mode') ?? $request->get('hub.mode');
        $token = $request->get('hub_verify_token') ?? $request->get('hub.verify_token');
        $challenge = $request->get('hub_challenge') ?? $request->get('hub.challenge');

        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            return response($challenge, 200);
        }

        return response('Token inválido', 403);
    }

    public function recibir(Request $request)
    {
        $data = $request->all();

        Log::info('Webhook WhatsApp recibido', $data);

        $mensaje = $data['entry'][0]['changes'][0]['value']['messages'][0]
            ?? $data['value']['messages'][0]
            ?? null;

        if (!$mensaje) {
            Log::warning('Webhook recibido pero sin mensaje válido', $data);
            return response()->json(['ok' => true]);
        }

        $telefono = $mensaje['from'] ?? null;

        $texto = trim(
            $mensaje['text']['body']
                ?? $mensaje['interactive']['button_reply']['id']
                ?? $mensaje['interactive']['list_reply']['id']
                ?? ''
        );

        if (!$telefono || !$texto) {
            Log::warning('Mensaje sin teléfono o texto', [
                'telefono' => $telefono,
                'texto' => $texto,
                'mensaje' => $mensaje,
            ]);

            return response()->json(['ok' => true]);
        }

        $conversacion = WhatsappConversacion::firstOrCreate(
            ['telefono' => $telefono],
            ['estado_flujo' => 'esperando_dni']
        );

        WhatsappMensaje::create([
            'conversacion_id' => $conversacion->id,
            'cliente_id' => $conversacion->cliente_id,
            'telefono' => $telefono,
            'tipo' => 'entrante',
            'mensaje' => $texto,
        ]);

        $respuesta = $this->procesarMensaje($conversacion, $texto);

        if (is_array($respuesta) && ($respuesta['tipo'] ?? '') === 'botones') {
            $enviado = $this->enviarBotones(
                $telefono,
                $respuesta['mensaje'],
                $respuesta['botones']
            );

            $mensajeGuardado = $respuesta['mensaje'];
        } elseif (is_array($respuesta) && ($respuesta['tipo'] ?? '') === 'lista') {
            $enviado = $this->enviarLista($telefono, $respuesta);

            $mensajeGuardado = $respuesta['mensaje'];
        } else {
            $enviado = $this->enviarTexto($telefono, $respuesta);
            $mensajeGuardado = $respuesta;
        }

        WhatsappMensaje::create([
            'conversacion_id' => $conversacion->id,
            'cliente_id' => $conversacion->cliente_id,
            'telefono' => $telefono,
            'tipo' => 'saliente',
            'mensaje' => $mensajeGuardado,
        ]);

        return response()->json([
            'ok' => true,
            'enviado' => $enviado,
        ]);
    }

    private function procesarMensaje(WhatsappConversacion $conversacion, string $texto): string|array
    {
        $texto = trim($texto);
        $textoNormalizado = strtolower($texto);
        $textoLimpio = preg_replace('/\D/', '', $texto);

        if (in_array($textoNormalizado, ['menu', 'menú', 'inicio', 'volver', 'principal', 'men'])) {
            if (!$conversacion->documento) {
                $conversacion->update([
                    'estado_flujo' => 'esperando_dni',
                ]);

                return "👋 Para volver al menú principal, primero necesito validar tu cuenta.\n\nIngresá tu *DNI sin puntos ni espacios*.";
            }

            $conversacion->update([
                'estado_flujo' => 'esperando_opcion',
            ]);

            return $this->menuPrincipal($conversacion);
        }

        if (in_array($texto, ['opcion_transferencia', 'opcion_refinanciacion', 'opcion_sucursal', 'opcion_promesa'])) {
            if (!$conversacion->documento) {
                $conversacion->update([
                    'estado_flujo' => 'esperando_dni',
                ]);

                return "👋 Primero necesito validar tu cuenta.\n\nIngresá tu *DNI sin puntos ni espacios*.";
            }

            return $this->resolverOpcion($conversacion, $texto);
        }

        // Flujo de promesa de pago: esperando la fecha.
        if ($conversacion->estado_flujo === 'promesa_fecha') {
            $fecha = PromesaDePago::normalizarFecha($texto);

            if (!$fecha) {
                return "No pude entender la fecha. Probá de nuevo con el formato *dd/mm/aaaa* (ejemplo: *25/12/2026*).\n\nPara cancelar escribí *menu*.";
            }

            $conversacion->update([
                'estado_flujo' => 'promesa_observaciones:' . $fecha,
            ]);

            $fechaFormateada = Carbon::parse($fecha)->format('d/m/Y');

            return "✅ *Fecha registrada: {$fechaFormateada}*\n\n" .
                "Ahora escribinos las *observaciones* que quieras dejar registradas para tu promesa de pago.\n\n" .
                "Si no querés agregar nada, enviá *-*.";
        }

        // Flujo de promesa de pago: esperando las observaciones (la fecha viaja en el estado).
        if (str_starts_with((string) $conversacion->estado_flujo, 'promesa_observaciones:')) {
            $fecha = substr((string) $conversacion->estado_flujo, strlen('promesa_observaciones:'));
            $observaciones = in_array($textoNormalizado, ['-', 'no', 'nada', 'sin observaciones'])
                ? null
                : $texto;

            return $this->registrarPromesa($conversacion, $fecha, $observaciones);
        }

        if ($conversacion->estado_flujo === 'esperando_dni') {
            if (strlen($textoLimpio) < 8) {
                return "👋 Hola. Para poder consultar tu cuenta, por favor ingresá tu *DNI sin puntos ni espacios*.";
            }

            $cliente = $this->buscarClientePorDocumento($textoLimpio);

            if (!$cliente) {
                return "No encontramos un cliente asociado al DNI ingresado.\n\nVerificá el número ingresado o comunicate con un asesor de *Tarjeta Premier*.";
            }

            $conversacion->update([
                'documento' => $cliente->DNI,
                'cliente_id' => $cliente->DNI,
                'estado_flujo' => 'esperando_opcion',
            ]);

            return $this->menuPrincipal($conversacion);
        }

        if ($conversacion->estado_flujo === 'esperando_opcion') {
            return $this->menuPrincipal($conversacion);
        }

        return "👋 Para volver al menú principal escribí *menu*.\n\nTambién podés seleccionar nuevamente una opción desde el último menú enviado.";
    }

    private function menuPrincipal(WhatsappConversacion $conversacion): array
    {
        $cliente = null;

        if ($conversacion->documento) {
            $cliente = $this->buscarClientePorDocumento((string) $conversacion->documento);
        }

        $nombre = $cliente->NOMBRE ?? 'cliente';
        $deuda = number_format((float) ($cliente->SAL_TOT ?? 0), 0, ',', '.');

        return [
            'tipo' => 'lista',
            'mensaje' =>
                "✅ *DNI verificado correctamente*\n\n" .
                "Hola *{$nombre}* 👋\n\n" .
                "Registrás una deuda actual de *$ {$deuda}* con *Tarjeta Premier*.\n\n" .
                "Queremos ayudarte a regularizar tu situación de la forma más simple posible 😊\n\n" .
                "Seleccioná una opción:",
            'titulo_boton' => 'Ver opciones',
            'opciones' => [
                [
                    'id' => 'opcion_promesa',
                    'titulo' => 'Promesa de pago',
                    'descripcion' => 'Registrá la fecha en que vas a pagar',
                ],
                [
                    'id' => 'opcion_transferencia',
                    'titulo' => 'Transferir',
                    'descripcion' => 'Datos para pagar por transferencia',
                ],
                [
                    'id' => 'opcion_refinanciacion',
                    'titulo' => 'Refinanciar',
                    'descripcion' => 'Un asesor revisa tu cuenta',
                ],
                [
                    'id' => 'opcion_sucursal',
                    'titulo' => 'Sucursal',
                    'descripcion' => 'Pagá en cualquiera de nuestras sucursales',
                ],
            ],
        ];
    }

    private function resolverOpcion(WhatsappConversacion $conversacion, string $opcion): string
    {
        if ($opcion === 'opcion_promesa') {
            $conversacion->update([
                'estado_flujo' => 'promesa_fecha',
            ]);

            return "📅 *Promesa de pago*\n\n" .
                "Perfecto. Indicá la *fecha* en la que vas a realizar el pago.\n\n" .
                "Escribila con el formato *dd/mm/aaaa* (ejemplo: *25/12/2026*).\n\n" .
                "Para cancelar escribí *menu*.";
        }

        if ($opcion === 'opcion_transferencia') {
            $conversacion->update([
                'estado_flujo' => 'esperando_comprobante',
            ]);

            return "💳 *Pago por transferencia*\n\n" .
                "Podés cancelar tu deuda realizando una transferencia bancaria con estos datos:\n\n" .
                "*PREMIER CARD TARJETA REGIONAL SA*\n" .
                "*CUIT:* 30-70899027-9\n" .
                "*BANCO MACRO SA SUCURSAL AGUILARES*\n" .
                "*CUENTA CORRIENTE EN PESOS*\n" .
                "N° 360100100014348\n" .
                "*CBU* 2850601830001000143480 \n\n" .
                "Una vez realizada la transferencia, respondé este mensaje con el comprobante 📎 para que podamos registrar tu pago.\n\n" .
                "Para volver al menú principal escribí *menu*.";
        }

        if ($opcion === 'opcion_refinanciacion') {
            $conversacion->update([
                'estado_flujo' => 'derivar_asesor',
            ]);

            return "🤝 *Opciones de refinanciación*\n\n" .
                "Entendido. Vamos a derivar tu caso para que un asesor de *Tarjeta Premier* revise las opciones disponibles para tu cuenta.\n\n" .
                "También podés comunicarte al *3865386766* en horario de atención de *8:30 a 12:30 hs y de 17:00 a 20:30*.\n\n" .
                "Para volver al menú principal escribí *menu*.";
        }

        if ($opcion === 'opcion_sucursal') {
            $conversacion->update([
                'estado_flujo' => 'pago_sucursal',
            ]);

            return "🏢 *Pago en sucursal*\n\n" .
                "Perfecto. Te esperamos en cualquiera de nuestras sucursales para regularizar tu situación.\n\n" .
                "Recordá mencionar tu DNI al llegar para que podamos identificar tu cuenta rápidamente ✅\n\n" .
                "Para volver al menú principal escribí *menu*.";
        }

        return "No pude identificar la opción seleccionada. Escribí *menu* para volver al menú principal.";
    }

    /**
     * Registra la promesa de pago del cliente (fecha + observaciones) en la tabla
     * promesas de sqlpremier y marca el moroso como PROMESA DE PAGO.
     */
    private function registrarPromesa(WhatsappConversacion $conversacion, string $fecha, ?string $observaciones): string
    {
        $cliente = $this->buscarClientePorDocumento((string) $conversacion->documento);

        if (!$cliente) {
            $conversacion->update([
                'estado_flujo' => 'esperando_dni',
            ]);

            return "No pude validar tu cuenta. Ingresá tu *DNI sin puntos ni espacios* para volver a empezar.";
        }

        try {
            PromesaDePago::upsertDesdeMoroso($cliente, $fecha, $observaciones);
        } catch (\InvalidArgumentException $e) {
            $conversacion->update([
                'estado_flujo' => 'promesa_fecha',
            ]);

            return "Hubo un problema con la fecha ingresada. Escribila de nuevo con el formato *dd/mm/aaaa* (ejemplo: *25/12/2026*).\n\nPara cancelar escribí *menu*.";
        }

        DB::connection($this->connection)->table($this->tabla)
            ->where('DNI', $cliente->DNI)
            ->update([
                'ESTADO' => 'PROMESA DE PAGO',
            ]);

        $conversacion->update([
            'estado_flujo' => 'esperando_opcion',
        ]);

        $fechaFormateada = Carbon::parse($fecha)->format('d/m/Y');

        return "✅ *Promesa de pago registrada*\n\n" .
            "📅 Fecha: *{$fechaFormateada}*\n" .
            ($observaciones ? "📝 Observaciones: {$observaciones}\n" : "") .
            "\nGracias por tu compromiso con el pago. Un asesor hará el seguimiento.\n\n" .
            "Para volver al menú principal escribí *menu*.";
    }

    private function buscarClientePorDocumento(string $documento)
    {
        $sql = "
            SELECT *
            FROM {$this->tabla}
            WHERE DNI = ?
            LIMIT 1
        ";

        return DB::connection($this->connection)->selectOne($sql, [$documento]);
    }

    private function enviarTexto(string $telefono, string $mensaje): bool
    {
        $version = config('services.whatsapp.version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');

        $telefono = $this->normalizarTelefonoParaMeta($telefono);

        $response = Http::withToken($token)->post(
            "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $mensaje,
                ],
            ]
        );

        Log::info('Respuesta envío texto WhatsApp', [
            'telefono' => $telefono,
            'status' => $response->status(),
            'ok' => $response->successful(),
            'body' => $response->json(),
        ]);

        return $response->successful();
    }

    private function enviarBotones(string $telefono, string $mensaje, array $botones): bool
    {
        $version = config('services.whatsapp.version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');

        $telefono = $this->normalizarTelefonoParaMeta($telefono);

        $response = Http::withToken($token)->post(
            "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $mensaje,
                    ],
                    'action' => [
                        'buttons' => array_map(function ($boton) {
                            return [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => $boton['id'],
                                    'title' => mb_substr($boton['titulo'], 0, 20),
                                ],
                            ];
                        }, $botones),
                    ],
                ],
            ]
        );

        Log::info('Respuesta envío botones WhatsApp', [
            'telefono' => $telefono,
            'status' => $response->status(),
            'ok' => $response->successful(),
            'body' => $response->json(),
        ]);

        return $response->successful();
    }

    /**
     * Envía un mensaje interactivo de tipo lista (permite más de 3 opciones,
     * a diferencia de los botones que tienen un máximo de 3).
     */
    private function enviarLista(string $telefono, array $lista): bool
    {
        $version = config('services.whatsapp.version');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');

        $telefono = $this->normalizarTelefonoParaMeta($telefono);

        $response = Http::withToken($token)->post(
            "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => $lista['mensaje'] ?? '',
                    ],
                    'action' => [
                        'button' => $lista['titulo_boton'] ?? 'Ver opciones',
                        'sections' => [
                            [
                                'title' => 'Opciones',
                                'rows' => array_map(function ($opcion) {
                                    $row = [
                                        'id' => $opcion['id'],
                                        'title' => mb_substr($opcion['titulo'], 0, 24),
                                    ];

                                    if (!empty($opcion['descripcion'])) {
                                        $row['description'] = mb_substr($opcion['descripcion'], 0, 72);
                                    }

                                    return $row;
                                }, $lista['opciones'] ?? []),
                            ],
                        ],
                    ],
                ],
            ]
        );

        Log::info('Respuesta envío lista WhatsApp', [
            'telefono' => $telefono,
            'status' => $response->status(),
            'ok' => $response->successful(),
            'body' => $response->json(),
        ]);

        return $response->successful();
    }

    private function normalizarTelefonoParaMeta(string $telefono): string
    {
        $telefono = preg_replace('/\D+/', '', $telefono);

        if (str_starts_with($telefono, '549')) {
            return '54' . substr($telefono, 3);
        }

        return $telefono;
    }
}