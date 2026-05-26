<?php

namespace App\Http\Controllers;

use App\Models\ClienteCarta;
use App\Imports\ClientesCartasImport;
use Illuminate\Http\Request;
use TCPDF;
use Maatwebsite\Excel\Facades\Excel;

class CartasController extends Controller
{
    public function index()
    {
        $morosos = ClienteCarta::orderBy('id', 'desc')->get();

        return view('cartas.index', compact('morosos'));
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls,csv',
        ]);
    
        $import = new \App\Imports\ClientesCartasImport();
    
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('archivo_excel'));
    
        return redirect()
            ->route('cartas.index')
            ->with('success', 'Excel procesado. Registros importados: ' . $import->insertados);
    }

    public function generarPdf(Request $request)
    {
        $ids = $request->input('cartas_seleccionadas', []);

        if (empty($ids)) {
            return redirect()
                ->route('cartas.index')
                ->withErrors('Tenés que seleccionar al menos un moroso para generar la carta.');
        }

        $clientes = ClienteCarta::whereIn('id', $ids)->get();

        if ($clientes->isEmpty()) {
            return redirect()
                ->route('cartas.index')
                ->withErrors('No se encontraron clientes seleccionados.');
        }

        $pdf = new \TCPDF('P', 'mm', 'A4');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 1, 15);
        $pdf->SetFont('dejavusans', '', 10, true);
        $pdf->setFontSubsetting(true);
        $nuevomodulo = 1;

        foreach ($clientes as $cliente) {
            $pdf->AddPage();

            $html = view('morosos._carta_documento', 
                compact('clientes'))->render();

            $pdf->writeHTML($html, true, false, true, false, '');
        }

        return response($pdf->Output('cartas_documentadas.pdf', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="cartas_documentadas.pdf"');
    }

    public function limpiar()
    {
        ClienteCarta::truncate();

        return redirect()
            ->route('cartas.index')
            ->with('success', 'Listado de cartas limpiado correctamente.');
    }
}