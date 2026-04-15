<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mensaje
 * 
 * Tabla de mensajes - almacena mensajes intercambiados en conversaciones
 */
class Mensaje extends Model
{
    protected $table = 'tbl_mensaje';
    protected $primaryKey = 'id_mensaje';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_conversacion_fk',
        'id_remitente_fk',
        'cuerpo_mensaje',
        'leido_mensaje',
        'creado_mensaje',
        'actualizado_mensaje',
    ];

    protected $casts = [
        'leido_mensaje' => 'boolean',
        'creado_mensaje' => 'datetime',
        'actualizado_mensaje' => 'datetime',
    ];

    // Conversación a la que pertenece el mensaje
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class, 'id_conversacion_fk', 'id_conversacion');
    }

    // Usuario que envió el mensaje
    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_remitente_fk', 'id_usuario');
    }
}
