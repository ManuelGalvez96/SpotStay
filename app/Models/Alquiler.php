<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Alquiler
 * 
 * Tabla de alquileres - vincula propiedades con inquilinos y registra el período de alquiler
 */
class Alquiler extends Model
{
    protected $table = 'tbl_alquiler';
    protected $primaryKey = 'id_alquiler';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_propiedad_fk',
        'id_inquilino_fk',
        'id_admin_aprueba_fk',
        'fecha_inicio_alquiler',
        'fecha_fin_alquiler',
        'estado_alquiler',
        'aprobado_alquiler',
        'creado_alquiler',
        'actualizado_alquiler',
    ];

    protected $casts = [
        'fecha_inicio_alquiler' => 'date',
        'fecha_fin_alquiler' => 'date',
        'aprobado_alquiler' => 'datetime',
        'creado_alquiler' => 'datetime',
        'actualizado_alquiler' => 'datetime',
    ];

    // Propiedad siendo alquilada
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }

    // Inquilino que alquila la propiedad
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_inquilino_fk', 'id_usuario');
    }

    // Admin que aprueba el alquiler
    public function adminAprueba(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_admin_aprueba_fk', 'id_usuario');
    }

    // Contrato asociado a este alquiler
    public function contrato(): HasOne
    {
        return $this->hasOne(Contrato::class, 'id_alquiler_fk', 'id_alquiler');
    }

    // Pagos realizados durante este alquiler
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_alquiler_fk', 'id_alquiler');
    }

    // Cuotas mensuales de alquiler asociadas al contrato
    public function cuotas(): HasMany
    {
        return $this->hasMany(AlquilerCuota::class, 'id_alquiler_fk', 'id_alquiler');
    }
}
