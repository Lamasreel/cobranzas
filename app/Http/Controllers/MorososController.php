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
            SELECT c.*,
                l.nombre_corto AS localidad,
                e.id as id_estado,
                e.estado
            FROM cliente c
            LEFT JOIN localidad l ON c.localidad = l.id
            LEFT JOIN estado e ON c.estado = e.id
            ORDER BY c.dias DESC
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
            ->where('estado', 'Pagado')
            ->value('id');

        DB::table('cliente')
            ->where('id', $id)
            ->update([
                'estado' => $estadoPagado,
                'dias' => 0
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
        $localidades = DB::table('localidad')->get();

        $sql = "
                SELECT c.*,
                    l.nombre_corto AS localidad,
                    e.id as id_estado,
                    e.estado
                FROM cliente c
                LEFT JOIN localidad l ON c.localidad = l.id
                LEFT JOIN estado e ON c.estado = e.id
        ";

        $bindings = [];

        $estado = $request->input('estado', '');
        if ($estado !== '') {
            $sql .= " AND c.estado = ?";
            $bindings[] = $estado;
        }

        $periodo = $request->input('periodo', '');
        if ($periodo !== '' && array_key_exists($periodo, $periodos)) {
            $sql .= " AND c.dias_mora BETWEEN ? AND ?";
            $bindings[] = $periodos[$periodo]['min'];
            $bindings[] = $periodos[$periodo]['max'];
        }

        $localidad = $request->input('localidad', '');
        if ($localidad !== '') {
            $sql .= " AND c.localidad = ?";
            $bindings[] = $localidad;
        }

        $sql .= " ORDER BY c.dias DESC";

        $morosos = DB::select($sql, $bindings);

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
            switch ($m->id_estado) {
                case 4:
                    $totales['pagados'] += $m->saldo_total;
                    $conteo['pagados']++;
                    break;
        
                case 3:
                    $totales['promesa'] += $m->saldo_total;
                    $conteo['promesa']++;
                    break;
        
                case 2:
                    $totales['pendiente'] += $m->saldo_total;
                    $conteo['pendiente']++;
                    break;
            }
        }

        $prediccion = [
            'pagado_futuro' => $totales['pagados'] + $totales['promesa'],
            'pendiente_futuro' => $totales['pendiente'],
        ];


        return view('morosos.index', [
            'morosos' => $morosos,
            'estados' => $estados,
            'totales' => $totales, 
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