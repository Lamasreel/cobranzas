<?php

namespace App\Http\Controllers;

use App\Services\WhatsappSender;
use App\Support\WhatsappAutomationSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MorososExcelImport;

class MorososController extends Controller
{
    private string $connection = 'mysql_local';
    private string $tabla = 'maectas2';

    private function baseSelect()
    {
        return DB::connection($this->connection)->table($this->tabla)
            ->select([
                'id',
                'NOMBRE as nombre',
                'DNI as documento',
                'CALLE as calle',
                'LOCALIDAD as localidad',
                'TEL_PARTI as telefono',
                'TEL_MOVIL as telefono_1',
                'TEL_ALTE1 as telefono_2',
                'TEL_ALTE2 as telefono_3',
                'TEL_VECIN as telefono_vecino',
                'TEL_LABOR as telefono_laboral',
                'TEL_GARAN as telefono_garante',
                'EMPLEADOR as empleador',
                'DNI_CON as documento_conyuge',
                'NOM_CON as nombre_conyuge',
                'DNI_GAR1 as documento_garante_1',
                'NOM_GAR1 as nombre_garante_1',
                'DOM_GAR1 as domicilio_garante_1',
                'LOC_GAR1 as localidad_garante_1',
                'DNI_GAR2 as documento_garante_2',
                'NOM_GAR2 as nombre_garante_2',
                'DOM_GAR2 as domicilio_garante_2',
                'LOC_GAR2 as localidad_garante_2',
                'DIAS as dias',
                'FECH_ULTP as fecha_ultimo_pago',
                'DEUDA as deuda',
                'CAP_PRESTA as capital_prestado',
                'PAGADO as pagado',
                'SAL_TOT_VE as saldo_vencido',
                'INT_PUN as interes_punitorio',
                'SAL_TOT as saldo_total',
                'SAL_SIN_PU as saldo_sin_punitorio',
                'ESTADO as estado',
                'SACAR as sacar',
            ]);
    }

    private function esPagado($estado): bool
    {
        $estado = strtolower(trim((string) $estado));

        return str_contains($estado, 'pagad')
            || str_contains($estado, 'cancelad')
            || str_contains($estado, 'cobrad');
    }

    private function esPromesa($estado): bool
    {
        $estado = strtolower(trim((string) $estado));

        return str_contains($estado, 'promesa')
            || str_contains($estado, 'compromiso');
    }

    public function index(Request $request): View
    {
        $periodos = [
            '0_30' => ['label' => '0 a 30 días', 'min' => 0, 'max' => 30],
            '30_60' => ['label' => '30 a 60 días', 'min' => 31, 'max' => 60],
            '60_90' => ['label' => '60 a 90 días', 'min' => 61, 'max' => 90],
            '90_120' => ['label' => '90 a 120 días', 'min' => 91, 'max' => 120],
            '120_150' => ['label' => '120 a 150 días', 'min' => 121, 'max' => 150],
            '150_180' => ['label' => '150 a 180 días', 'min' => 151, 'max' => 180],
            '180_365' => ['label' => '180 a 365 días', 'min' => 181, 'max' => 365],
            '365_plus' => ['label' => '365+ días', 'min' => 366, 'max' => 99999],
        ];

        $query = $this->baseSelect();

        $estado = trim((string) $request->input('estado', ''));
        if ($estado !== '') {
            $query->where('ESTADO', 'LIKE', '%' . $estado . '%');
        }

        $periodo = trim((string) $request->input('periodo', ''));
        $periodoSeleccionado = null;

        if ($periodo !== '' && isset($periodos[$periodo])) {
            $rango = $periodos[$periodo];

            $query->whereBetween('DIAS', [$rango['min'], $rango['max']]);
            $periodoSeleccionado = (object) [
                'id' => $periodo,
                'mes' => $rango['label'],
            ];
        }

        $localidad = trim((string) $request->input('localidad', ''));
        if ($localidad !== '') {
            $query->where('LOCALIDAD', $localidad);
        }

        $morosos = $query
            ->orderByDesc('DIAS')
            ->get();

        $localidades = DB::connection($this->connection)->table($this->tabla)
            ->select('LOCALIDAD as nombre_corto')
            ->whereNotNull('LOCALIDAD')
            ->where('LOCALIDAD', '<>', '')
            ->distinct()
            ->orderBy('LOCALIDAD')
            ->get();

        $estados = DB::connection($this->connection)->table($this->tabla)
            ->select('ESTADO as estado')
            ->whereNotNull('ESTADO')
            ->where('ESTADO', '<>', '')
            ->distinct()
            ->orderBy('ESTADO')
            ->get();

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
            $saldo = (float) ($m->saldo_vencido ?? 0) + (float) ($m->interes_punitorio ?? 0);

            if ($this->esPagado($m->estado ?? '')) {
                $totales['pagados'] += $saldo;
                $conteo['pagados']++;
            } elseif ($this->esPromesa($m->estado ?? '')) {
                $totales['promesa'] += $saldo;
                $conteo['promesa']++;
            } else {
                $totales['pendiente'] += $saldo;
                $conteo['pendiente']++;
            }
        }

        $prediccion = [
            'pagado_futuro' => $totales['pagados'] + $totales['promesa'],
            'pendiente_futuro' => $totales['pendiente'],
        ];

        $resumenPorRango = [];

        foreach ($periodos as $key => $r) {
            $filtrados = $morosos->filter(function ($m) use ($r) {
                return (int) ($m->dias ?? 0) >= $r['min']
                    && (int) ($m->dias ?? 0) <= $r['max'];
            });

            $resumenPorRango[$key] = $filtrados
                ->groupBy('localidad')
                ->map(function ($items) {
                    $total = $items->count();

                    $pagaron = $items->filter(function ($row) {
                        return $this->esPagado($row->estado ?? '');
                    })->count();

                    return [
                        'total' => $total,
                        'tit' => $total,
                        'gar' => 0,
                        'pagaron' => $pagaron,
                        'wsp' => $items->filter(function ($i) {
                            return !empty($i->telefono_1);
                        })->count(),
                        'no_wsp' => $items->filter(function ($i) {
                            return empty($i->telefono_1);
                        })->count(),
                        'no_tel' => $items->filter(function ($i) {
                            return empty($i->telefono)
                                && empty($i->telefono_1)
                                && empty($i->telefono_2)
                                && empty($i->telefono_3);
                        })->count(),
                        'carta' => 0,
                    ];
                });
        }

        $habilitarCartas = false;

        if ($periodoSeleccionado) {
            $textoMes = strtolower((string) ($periodoSeleccionado->mes ?? ''));

            if (
                str_contains($textoMes, '90') ||
                str_contains($textoMes, '120') ||
                str_contains($textoMes, '150') ||
                str_contains($textoMes, '180') ||
                str_contains($textoMes, '365')
            ) {
                $habilitarCartas = true;
            }
        }

        return view('morosos.index', [
            'morosos' => $morosos,
            'estados' => $estados,
            'estadoIdPagado' => null,
            'estadoIdPromesa' => null,
            'periodo_mora' => collect($periodos)->map(function ($p, $key) {
                return (object) [
                    'id' => $key,
                    'mes' => $p['label'],
                ];
            })->values(),
            'totales' => $totales,
            'resumen' => $resumenPorRango,
            'rangos' => $periodos,
            'conteo' => $conteo,
            'prediccion' => $prediccion,
            'periodos' => $periodos,
            'localidades' => $localidades,
            'whatsappAuto' => WhatsappAutomationSettings::read(),
            'habilitarCartas' => $habilitarCartas,
            'filters' => [
                'estado' => $estado,
                'periodo' => $periodo,
                'localidad' => $localidad,
            ],
        ]);
    }

    public function generarPDF()
    {
        $morosos = $this->baseSelect()
            ->orderByDesc('DIAS')
            ->get();

        $html = view('morosos._carta_documento', compact('morosos'))->render();

        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 1, 15);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10, true);
        $pdf->setFontSubsetting(true);
        $pdf->writeHTML($html, false, false, false, false, '');

        return response($pdf->Output('morosos.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    public function generarPDFSeleccionados(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $morosos = $this->baseSelect()
            ->whereIn('id', $data['ids'])
            ->where('DIAS', '>', 90)
            ->orderByDesc('DIAS')
            ->get();

        $html = view('morosos._carta_documento', compact('morosos'))->render();

        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 1, 15);
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10, true);
        $pdf->setFontSubsetting(true);
        $pdf->writeHTML($html, false, false, false, false, '');

        return response($pdf->Output('cartas_documentadas.pdf', 'S'))
            ->header('Content-Type', 'application/pdf');
    }

    public function marcarPagado($id)
    {
        DB::connection($this->connection)->table($this->tabla)
            ->where('id', $id)
            ->update([
                'ESTADO' => 'PAGADO',
            ]);

        return back();
    }

    public function marcarPagadosMasivo(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        DB::connection($this->connection)->table($this->tabla)
            ->whereIn('id', $data['ids'])
            ->update([
                'ESTADO' => 'PAGADO',
            ]);

        return back();
    }

    public function actualizarPromesa(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        DB::connection($this->connection)->table($this->tabla)
            ->where('id', $data['id'])
            ->update([
                'ESTADO' => 'PROMESA DE PAGO',
            ]);

        return back();
    }

    public function subirExcel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new MorososExcelImport, $request->file('archivo'));

        return redirect()
            ->route('morosos.index')
            ->with('success', 'Excel importado correctamente.');
    }

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
                'message' => 'Faltan variables de entorno token/phone_number_id/to.',
            ], 422);
        }
    
        $params = [
            'Luciano',
            '$100.000',
            '90',
        ];
    
        $result = $whatsapp->sendTemplate($to, $template, $lang, $params);
    
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
            'time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
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