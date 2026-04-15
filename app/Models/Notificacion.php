<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificacion
 * 
 * Tabla de notificaciones - almacena notificaciones del sistema para los usuarios
 */
class Notificacion extends Model
{
    protected $table = 'tbl_notificacion';
    protected $primaryKey = 'id_notificacion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'tipo_notificacion',
        'datos_notificacion',
        'leida_notificacion',
        'leida_en_notificacion',
        'creado_notificacion',
        'actualizado_notificacion',
    ];

    protected $casts = [
        'datos_notificacion' => 'array',
        'leida_notificacion' => 'boolean',
        'leida_en_notificacion' => 'datetime',
        'creado_notificacion' => 'datetime',
        'actualizado_notificacion' => 'datetime',
    ];

    // Usuario que recibe la notificación
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
