<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandadoController extends Controller
{
    public function index()
    {
        $datosDemandados = "
            SELECT *
            FROM demandados
            ORDER BY id DESC
        ";

        $demandados = DB::select($datosDemandados);
        $demandadosCantidad = DB::table('demandados')->count();

        return view('demandado.index', [
            'demandados' => $demandados,
            'demandadosCantidad' => $demandadosCantidad
        ]);
    }
}