<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ConversacionUsuario
 * 
 * Tabla pivot de conversacion-usuario - almacena la relación entre usuarios y conversaciones
 */
class ConversacionUsuario extends Model
{
    protected $table = 'tbl_conversacion_usuario';
    protected $primaryKey = 'id_conversacion_usuario';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_conversacion_fk',
        'id_usuario_fk',
        'ultima_lectura_conv_usuario',
    ];

    protected $casts = [
        'ultima_lectura_conv_usuario' => 'datetime',
    ];

    // Conversación en la que participa el usuario
    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class, 'id_conversacion_fk', 'id_conversacion');
    }

    // Usuario participante en la conversación
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
