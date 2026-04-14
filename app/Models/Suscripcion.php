<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Suscripcion
 * 
 * Tabla de suscripciones - almacena los planes de suscripción de usuarios arrendadores
 */
class Suscripcion extends Model
{
    protected $table = 'tbl_suscripcion';
    protected $primaryKey = 'id_suscripcion';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'plan_suscripcion',
        'max_propiedades_suscripcion',
        'inicio_suscripcion',
        'fin_suscripcion',
        'estado_suscripcion',
        'creado_suscripcion',
        'actualizado_suscripcion',
    ];

    protected $casts = [
        'max_propiedades_suscripcion' => 'integer',
        'inicio_suscripcion' => 'date',
        'fin_suscripcion' => 'date',
        'creado_suscripcion' => 'datetime',
        'actualizado_suscripcion' => 'datetime',
    ];

    // Usuario propietario de la suscripción
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
