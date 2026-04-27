<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPropiedad extends Model
{
    protected $table = 'tbl_historial_propiedad';
    protected $primaryKey = 'id_historial_propiedad';
    public $timestamps = false;

    protected $fillable = [
        'id_propiedad_fk',
        'id_usuario_fk',
        'tipo_cambio_historial',
        'campo_modificado_historial',
        'valor_anterior_historial',
        'valor_nuevo_historial',
        'estado_anterior_historial',
        'estado_nuevo_historial',
        'comentario_historial',
        'creado_historial',
    ];

    protected $dates = [
        'creado_historial',
    ];

    /**
     * Relación: Un historial pertenece a una propiedad
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }

    /**
     * Relación: Un historial pertenece a un usuario (quien realizó el cambio)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
