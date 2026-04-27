<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'tbl_plan';
    protected $primaryKey = 'id_plan';
    public $timestamps = false;

    protected $fillable = [
        'nombre_plan',
        'slug_plan',
        'precio_plan',
        'max_propiedades_plan',
        'descripcion_plan',
        'activo_plan',
        'creado_plan',
        'actualizado_plan',
    ];

    protected $casts = [
        'precio_plan' => 'decimal:2',
        'activo_plan' => 'boolean',
        'creado_plan' => 'datetime',
        'actualizado_plan' => 'datetime',
    ];

    /**
     * Relación: Un plan tiene muchas suscripciones
     */
    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'id_plan_fk', 'id_plan');
    }
}
