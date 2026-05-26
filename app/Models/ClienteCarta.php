<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteCarta extends Model
{
    protected $table = 'clientes_cartas';

    public $timestamps = false;

    protected $fillable = [
        'documento_titular',
        'documento',
        'nombre',
        'calle',
        'observaciones',
        'localidad',
        'seleccionado',
    ];
}