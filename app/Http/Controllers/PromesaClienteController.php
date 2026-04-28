<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromesaClienteController extends Controller
{
    public function index(Request $request): View
    {
        $documento = trim((string) $request->query('documento', ''));
        $cliente = null;

        if ($documento !== '') {
            $cliente = DB::table('cliente')
                ->where('documento', $documento)
                ->first();
        }

        return view('promesa_cliente', [
            'documento' => $documento,
            'cliente' => $cliente,
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'documento' => ['required', 'string'],
            'fecha_promesa_pago' => ['required', 'date', 'after_or_equal:today'],
            'observaciones_promesa' => ['nullable', 'string', 'max:2000'],
        ], [
            'fecha_promesa_pago.after_or_equal' => 'La fecha de promesa no puede ser anterior a hoy.',
        ]);
    
        $cliente = DB::table('cliente')
            ->where('documento', $data['documento'])
            ->first();
    
        if (!$cliente) {
            return back()
                ->withInput()
                ->with('error', 'No se encontró un cliente con ese documento.');
        }
    
        if (!empty($cliente->fecha_promesa_pago) || (int) $cliente->estado === 3) {
            return back()
                ->withInput()
                ->with('error', 'Este cliente ya tiene una promesa de pago cargada.');
        }
    
        DB::table('cliente')
            ->where('documento', $data['documento'])
            ->update([
                'fecha_promesa_pago' => $data['fecha_promesa_pago'],
                'observaciones_promesa' => $data['observaciones_promesa'] ?? null,
                'estado' => 3,
            ]);
    
        return redirect()
            ->route('promesa_cliente', ['documento' => $data['documento']])
            ->with('success', 'Promesa guardada.');
    }
}