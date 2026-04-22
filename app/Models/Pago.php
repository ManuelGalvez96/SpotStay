<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pago
 * 
 * Tabla de pagos - registra los movimientos monetarios de alquileres
 */
class Pago extends Model
{
    protected $table = 'tbl_pago';
    protected $primaryKey = 'id_pago';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_alquiler_fk',
        'id_alquiler_cuota_fk',
        'id_pagador_fk',
        'id_gasto_cuota_detalle_fk',
        'id_gasto_cuota_fk',
        'tipo_pago',
        'concepto_pago',
        'importe_pago',
        'mes_pago',
        'estado_pago',
        'referencia_pago',
        'fecha_confirmacion_pago',
        'creado_pago',
        'actualizado_pago',
    ];

    protected $casts = [
        'importe_pago' => 'decimal:2',
        'mes_pago' => 'date',
        'fecha_confirmacion_pago' => 'datetime',
        'creado_pago' => 'datetime',
        'actualizado_pago' => 'datetime',
    ];

    // Alquiler al que corresponde este pago
    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler_fk', 'id_alquiler');
    }

    // Cuota de alquiler asociada (nuevo flujo de alquiler mensual)
    public function alquilerCuota(): BelongsTo
    {
        return $this->belongsTo(AlquilerCuota::class, 'id_alquiler_cuota_fk', 'id_alquiler_cuota');
    }

    // Usuario que realizó el pago
    public function pagador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_pagador_fk', 'id_usuario');
    }
}
