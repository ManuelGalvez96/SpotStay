<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ChatbotSesion
 * 
 * Tabla de sesiones del chatbot - almacena las conversaciones del usuario con el asistente
 */
class ChatbotSesion extends Model
{
    protected $table = 'tbl_chatbot_sesion';
    protected $primaryKey = 'id_sesion_chatbot';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'creado_sesion_chatbot',
        'actualizado_sesion_chatbot',
    ];

    protected $casts = [
        'creado_sesion_chatbot' => 'datetime',
        'actualizado_sesion_chatbot' => 'datetime',
    ];

    // Usuario propietario de la sesión del chatbot
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }

    // Mensajes de la sesión ordenados por fecha de creación ascendente
    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatbotMensaje::class, 'id_sesion_chatbot_fk', 'id_sesion_chatbot')
            ->orderBy('creado_mensaje_chatbot', 'asc');
    }
}
