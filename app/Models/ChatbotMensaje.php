<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ChatbotMensaje
 * 
 * Tabla de mensajes del chatbot - almacena cada mensaje intercambiado en una sesión del chatbot
 */
class ChatbotMensaje extends Model
{
    protected $table = 'tbl_chatbot_mensaje';
    protected $primaryKey = 'id_mensaje_chatbot';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_sesion_chatbot_fk',
        'rol_mensaje_chatbot',
        'cuerpo_mensaje_chatbot',
        'creado_mensaje_chatbot',
    ];

    protected $casts = [
        'creado_mensaje_chatbot' => 'datetime',
    ];

    // Sesión de chatbot a la que pertenece este mensaje
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(ChatbotSesion::class, 'id_sesion_chatbot_fk', 'id_sesion_chatbot');
    }
}
