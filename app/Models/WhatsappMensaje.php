<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMensaje extends Model
{
    protected $connection = 'mysql_local';

    protected $table = 'whatsapp_mensajes';

    protected $fillable = [
        'conversacion_id',
        'cliente_id',
        'telefono',
        'tipo',
        'mensaje',
    ];
    use HasFactory;
}
