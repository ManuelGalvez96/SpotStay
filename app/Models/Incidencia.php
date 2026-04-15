<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Incidencia
 * 
 * Tabla de incidencias - almacena problemas o reportes relacionados con propiedades
 */
class Incidencia extends Model
{
    protected $table = 'tbl_incidencia';
    protected $primaryKey = 'id_incidencia';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_propiedad_fk',
        'id_reporta_fk',
        'id_asignado_fk',
        'titulo_incidencia',
        'descripcion_incidencia',
        'categoria_incidencia',
        'prioridad_incidencia',
        'estado_incidencia',
        'creado_incidencia',
        'actualizado_incidencia',
    ];

    protected $casts = [
        'creado_incidencia' => 'datetime',
        'actualizado_incidencia' => 'datetime',
    ];

    // Propiedad asociada a la incidencia
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }

    // Usuario que reporta la incidencia
    public function reportadaPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_reporta_fk', 'id_usuario');
    }

    // Usuario asignado para resolver la incidencia
    public function asignadaA(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_asignado_fk', 'id_usuario');
    }

    // Historial de actualizaciones de la incidencia ordenado por fecha de creación ascendente
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialIncidencia::class, 'id_incidencia_fk', 'id_incidencia')
            ->orderBy('creado_historial', 'asc');
    }
}
