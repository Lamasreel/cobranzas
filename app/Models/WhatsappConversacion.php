<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappConversacion extends Model
{
    protected $table = 'whatsapp_conversaciones';

    protected $fillable = [
        'telefono',
        'documento',
        'cliente_id',
        'estado_flujo',
    ];
    use HasFactory;
}
