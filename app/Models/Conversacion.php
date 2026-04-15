<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Conversacion
 * 
 * Tabla de conversaciones - almacena conversaciones entre usuarios sobre propiedades
 */
class Conversacion extends Model
{
    protected $table = 'tbl_conversacion';
    protected $primaryKey = 'id_conversacion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_propiedad_fk',
        'tipo_conversacion',
        'creado_conversacion',
        'actualizado_conversacion',
    ];

    protected $casts = [
        'creado_conversacion' => 'datetime',
        'actualizado_conversacion' => 'datetime',
    ];

    // Propiedad asociada a la conversación (puede ser nullable)
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }

    // Participantes en la conversación con información del última lectura
    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'tbl_conversacion_usuario',
            'id_conversacion_fk',
            'id_usuario_fk',
            'id_conversacion',
            'id_usuario'
        )->withPivot('ultima_lectura_conv_usuario');
    }

    // Mensajes de la conversación ordenados por fecha de creación ascendente
    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'id_conversacion_fk', 'id_conversacion')
            ->orderBy('creado_mensaje', 'asc');
    }

    // Último mensaje de la conversación ordenado por fecha descendente
    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class, 'id_conversacion_fk', 'id_conversacion')
            ->orderBy('creado_mensaje', 'desc');
    }
}
