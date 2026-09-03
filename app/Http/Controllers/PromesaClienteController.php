<?php

namespace App\Http\Controllers;

use App\Support\PromesaDePago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PromesaClienteController extends Controller
{
    private string $connection = 'mysql_local';
    private string $tabla = 'morosos';

    public function index(Request $request): View
    {
        $documento = trim((string) $request->query('documento', ''));
        $cliente = null;

        if ($documento !== '') {
            $cliente = $this->buscarCliente($documento);
        }

        return view('promesa_cliente', [
            'documento' => $documento,
            'cliente' => $cliente,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'documento' => ['required'],
            'fecha_promesa_pago' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $fecha = PromesaDePago::normalizarFecha($value);

                    if ($fecha === null) {
                        $fail('La fecha de promesa no es válida (usá formato dd/mm/aaaa o aaaa-mm-dd).');
                        return;
                    }

                    if ($fecha < now()->toDateString()) {
                        $fail('La fecha de promesa no puede ser anterior a hoy.');
                    }
                },
            ],
            'observaciones_promesa' => ['nullable', 'string', 'max:2000'],
        ]);

        $moroso = DB::connection($this->connection)->table($this->tabla)
            ->where('DNI', trim((string) $data['documento']))
            ->first();

        if (!$moroso) {
            return back()
                ->withInput()
                ->with('error', 'No se encontró un cliente con ese documento.');
        }

        if (PromesaDePago::tienePromesaPendiente((string) $moroso->DNI)) {
            return back()
                ->withInput()
                ->with('error', 'Este cliente ya tiene una promesa de pago cargada.');
        }

        PromesaDePago::upsertDesdeMoroso(
            $moroso,
            $data['fecha_promesa_pago'],
            $data['observaciones_promesa'] ?? null
        );

        DB::connection($this->connection)->table($this->tabla)
            ->where('DNI', $moroso->DNI)
            ->update([
                'ESTADO' => 'PROMESA DE PAGO',
            ]);

        return redirect()
            ->route('promesa_cliente', ['documento' => $data['documento']])
            ->with('success', 'Promesa guardada.');
    }

    /**
     * Busca el moroso por DNI en sqlpremier y lo normaliza para la vista:
     * documento, nombre y, si tiene una promesa pendiente en promesas_pago,
     * su fecha_promesa_pago y observaciones_promesa.
     */
    private function buscarCliente(string $documento): ?object
    {
        $moroso = DB::connection($this->connection)->table($this->tabla)
            ->where('DNI', $documento)
            ->first();

        if (!$moroso) {
            return null;
        }

        $pendiente = PromesaDePago::promesaPendiente((string) $moroso->DNI);

        return (object) [
            'documento' => $moroso->DNI,
            'nombre' => $moroso->NOMBRE,
            'fecha_promesa_pago' => $pendiente->fecha_prometida ?? null,
            'observaciones_promesa' => $pendiente->observaciones ?? null,
        ];
    }
}
