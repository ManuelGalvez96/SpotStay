<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * HistorialIncidencia
 * 
 * Tabla de historial de incidencias - registra cambios y comentarios en las incidencias
 */
class HistorialIncidencia extends Model
{
    protected $table = 'tbl_historial_incidencia';
    protected $primaryKey = 'id_historial_incidencia';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_incidencia_fk',
        'id_usuario_fk',
        'comentario_historial',
        'cambio_estado_historial',
        'creado_historial',
        'actualizado_historial',
    ];

    protected $casts = [
        'creado_historial' => 'datetime',
        'actualizado_historial' => 'datetime',
    ];

    // Incidencia a la que pertenece este historial
    public function incidencia(): BelongsTo
    {
        return $this->belongsTo(Incidencia::class, 'id_incidencia_fk', 'id_incidencia');
    }

    // Usuario que realizó el cambio
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
