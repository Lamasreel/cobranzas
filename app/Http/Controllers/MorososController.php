<?php

namespace App\Http\Controllers;

use App\Services\WhatsappSender;
use TCPDF;
use App\Support\WhatsappAutomationSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
const ESTADO_PENDIENTE = 2;
const ESTADO_PROMESA = 3;
const ESTADO_PAGADO = 4;

class MorososController extends Controller
{
    public function enviarWhatsappTest(Request $request, WhatsappSender $whatsapp)
    {
        $token = (string) config('services.whatsapp.token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $to = (string) config('services.whatsapp.to');
        $template = (string) config('services.whatsapp.template');
        $lang = (string) config('services.whatsapp.lang');

        if ($token === '' || $phoneNumberId === '' || $to === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Faltan variables de entorno (token/phone_number_id/to).',
            ], 422);
        }

        $result = $whatsapp->sendTemplate($to, $template, $lang, null);

        if ($result['exception']) {
            return response()->json([
                'ok' => false,
                'message' => 'Error de conexión al enviar WhatsApp.',
                'exception' => $result['exception'],
            ], 500);
        }

        if (!$result['ok']) {
            return response()->json([
                'ok' => false,
                'message' => 'Error enviando WhatsApp.',
                'status' => $result['status'],
                'error' => $result['json'],
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Mensaje enviado.',
            'data' => $result['json'],
        ]);
    }

    public function generarPDF()
    {

        $sql = "
        SELECT 
            c.*,
            t.nombre AS nombre_titular,
            l.nombre_corto AS localidad,
            e.id as id_estado,
            e.estado
        FROM cliente c
        LEFT JOIN cliente t ON c.documento_titular = t.documento
        LEFT JOIN localidad l ON c.localidad = l.id
        LEFT JOIN estado e ON c.estado = e.id
        ORDER BY c.titular_garantia ASC, c.dias DESC
    ";
    
        $morosos = DB::select($sql);
    
        $html = view('morosos._carta_documento', compact('morosos'))->render();
    
        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 1, 15);
        $pdf->AddPage();
        // $img = base_path('assets/images/firma_abogado.png');
        // $pdf->Image($img, 150, 110, 40); 
        $pdf->SetFont('dejavusans', '', 10, true); //dejavusans helvetica
        $pdf->setFontSubsetting(true);
        $pdf->writeHTML($html, false, false, false, false, '');
    
        return response($pdf->Output('morosos.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }
    public function actualizarPromesa(Request $request)
    {
        DB::table('cliente')
            ->where('id', $request->id)
            ->update([
                'fecha_promesa_pago' => $request->fecha_promesa_pago,
                'observaciones_promesa' => $request->observaciones_promesa,
                'estado' => 3 
            ]);

        return back();
    }

    public function marcarPagado($id)
    {
        $estadoPagado = DB::table('estado')
            ->whereRaw('LOWER(estado) LIKE ?', ['%pagad%'])
            ->value('id');

        DB::table('cliente')
            ->where('id', $id)
            ->update([
                'estado' => $estadoPagado ?? ESTADO_PAGADO,
            ]);

        return back();
    }

    public function marcarPagadosMasivo(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $estadoPagado = DB::table('estado')
            ->whereRaw('LOWER(estado) LIKE ?', ['%pagad%'])
            ->value('id');

        DB::table('cliente')
            ->whereIn('id', $data['ids'])
            ->update([
                'estado' => $estadoPagado ?? ESTADO_PAGADO,
            ]);

        return back();
    }

    public function index(Request $request): View
    {
        $periodos = [
            '30_60' => ['label' => '30 a 60 días', 'min' => 30, 'max' => 60],
            '60_90' => ['label' => '60 a 90 días', 'min' => 60, 'max' => 90],
            '120_180' => ['label' => '120 a 180 días', 'min' => 120, 'max' => 180],
            '180_365' => ['label' => '180 a 365 días', 'min' => 180, 'max' => 365],
        ];

        $estados = DB::table('estado')->get();
        $estadoIdPagado = $estados->first(function ($e) {
            $t = strtolower(trim((string) ($e->estado ?? '')));

            return str_contains($t, 'pagad');
        })?->id;
        $estadoIdPromesa = $estados->first(function ($e) {
            $t = strtolower(trim((string) ($e->estado ?? '')));

            return str_contains($t, 'promesa')
                && ! str_contains($t, 'sin promesa');
        })?->id;
        $localidades = DB::table('localidad')->get();

        $sql = "
                SELECT c.*,
                    c.estado as estado_id_cliente,
                    l.nombre_corto AS localidad,
                    e.id as id_estado,
                    e.estado
                FROM cliente c
                LEFT JOIN localidad l ON c.localidad = l.id
                LEFT JOIN estado e ON c.estado = e.id
                WHERE 1=1
        ";

        $bindings = [];

        $estado = trim((string) $request->input('estado', ''));
        if (!in_array($estado, [(string) ESTADO_PENDIENTE, (string) ESTADO_PROMESA, (string) ESTADO_PAGADO], true)) {
            $estado = '';
        }
        if ($estado !== '') {
            $sql .= " AND c.estado = ?";
            $bindings[] = (int) $estado;
        }

        $periodo = $request->input('periodo', '');
        if ($periodo !== '' && array_key_exists($periodo, $periodos)) {
            $sql .= " AND c.dias_mora BETWEEN ? AND ?";
            $bindings[] = $periodos[$periodo]['min'];
            $bindings[] = $periodos[$periodo]['max'];
        }

        $localidad = trim((string) $request->input('localidad', ''));
        if ($localidad !== '' && ctype_digit($localidad)) {
            $sql .= " AND c.localidad = ?";
            $bindings[] = (int) $localidad;
        }

        $sql .= " ORDER BY c.dias DESC";

        $morosos = collect(DB::select($sql, $bindings));

        $totales = [
            'pagados' => 0,
            'promesa' => 0,
            'pendiente' => 0,
        ];
        
        $conteo = [
            'pagados' => 0,
            'promesa' => 0,
            'pendiente' => 0,
        ];
        
        foreach ($morosos as $m) {
            $estNombre = strtolower(trim((string) ($m->estado ?? '')));
            $idEst = (int) ($m->estado_id_cliente ?? $m->id_estado ?? 0);

            $esPagadoFila = $idEst === ESTADO_PAGADO
                || ($estadoIdPagado !== null && $idEst === (int) $estadoIdPagado)
                || str_contains($estNombre, 'pagad');
            $esPromesaFila = ! $esPagadoFila && (
                $idEst === ESTADO_PROMESA
                || ($estadoIdPromesa !== null && $idEst === (int) $estadoIdPromesa)
                || (str_contains($estNombre, 'promesa') && ! str_contains($estNombre, 'sin promesa'))
            );

            if ($esPagadoFila) {
                $totales['pagados'] += $m->saldo_total;
                $conteo['pagados']++;
                continue;
            }
            if ($esPromesaFila) {
                $totales['promesa'] += $m->saldo_total;
                $conteo['promesa']++;
                continue;
            }
            if ($idEst === ESTADO_PENDIENTE || str_contains($estNombre, 'pendiente')) {
                $totales['pendiente'] += $m->saldo_total;
                $conteo['pendiente']++;
            }
        }

        $prediccion = [
            'pagado_futuro' => $totales['pagados'] + $totales['promesa'],
            'pendiente_futuro' => $totales['pendiente'],
        ];
        
        $rangos = [
            '0_30' => ['label' => '0-30 días', 'min' => 0, 'max' => 30],
            '30_60' => ['label' => '30-60 días', 'min' => 30, 'max' => 60],
            '60_90' => ['label' => '60-90 días', 'min' => 60, 'max' => 90],
            '90_120' => ['label' => '90-120 días', 'min' => 90, 'max' => 120],
            '120_150' => ['label' => '120-150 días', 'min' => 120, 'max' => 150],
            '150_180' => ['label' => '150-180 días', 'min' => 150, 'max' => 180],
            '180_365' => ['label' => '180-365', 'min' => 181, 'max' => 365],
            '365_plus' => ['label' => '365+ días', 'min' => 365, 'max' => 99999],
        ];
    
        $resumenPorRango = [];
    
        foreach ($rangos as $key => $r) {
    
            $filtrados = $morosos->filter(function ($m) use ($r) {
                return $m->dias >= $r['min'] && $m->dias <= $r['max'];
            });
    
            $resumenPorRango[$key] = $filtrados
                ->groupBy('localidad')
                ->map(function ($items) use ($estadoIdPagado) {

                    $total = $items->count();
                    $pagaron = $items->filter(function ($row) use ($estadoIdPagado) {
                        $n = strtolower(trim((string) ($row->estado ?? '')));
                        $id = (int) ($row->estado_id_cliente ?? $row->id_estado ?? 0);

                        return $id === ESTADO_PAGADO
                            || ($estadoIdPagado !== null && $id === (int) $estadoIdPagado)
                            || str_contains($n, 'pagad');
                    })->count();
    
                    return [
                        'total' => $total,
                        'tit' => $items->where('titular_garantia', 1)->count(),
                        'gar' => $items->where('titular_garantia', 2)->count(),
                        'pagaron' => $pagaron,
                        'wsp' => $items->where('tiene_wsp', 1)->count(),
                        'no_wsp' => $items->where('tiene_wsp', '!=', 1)->count(),
                        'no_tel' => $items->filter(fn($i) => empty($i->telefono))->count(),
                        'carta' => $items->where('carta', 1)->count(),
                    ];
                });
        }

        return view('morosos.index', [
            'morosos' => $morosos,
            'estados' => $estados,
            'estadoIdPagado' => $estadoIdPagado,
            'estadoIdPromesa' => $estadoIdPromesa,
            'totales' => $totales, 
            'resumen' => $resumenPorRango, 
            'rangos' => $rangos, 
            'conteo' => $conteo, 
            'prediccion' => $prediccion, 
            'periodos' => $periodos,
            'localidades' => $localidades,
            'whatsappAuto' => WhatsappAutomationSettings::read(),
            'filters' => [
                'estado' => $estado,
                'periodo' => $periodo,
                'localidad' => $localidad,
            ],
        ]);
    }

    public function whatsappAutomationShow()
    {
        return response()->json([
            'ok' => true,
            'settings' => WhatsappAutomationSettings::read(),
        ]);
    }

    public function whatsappAutomationUpdate(Request $request)
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'time' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
        ]);

        WhatsappAutomationSettings::write([
            'enabled' => (bool) $data['enabled'],
            'day_of_month' => (int) $data['day_of_month'],
            'time' => (string) $data['time'],
        ]);

        return response()->json([
            'ok' => true,
            'settings' => WhatsappAutomationSettings::read(),
        ]);
    }
}