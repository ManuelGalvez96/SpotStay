<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionGestor extends Model
{
    protected $table = 'tbl_asignacion_gestor';
    protected $primaryKey = 'id_asignacion_gestor';
    public $timestamps = false;
    
    protected $fillable = [
        'id_propiedad_fk',
        'id_gestor_fk',
        'id_arrendador_asigno_fk',
        'estado_asignacion',
        'notas_asignacion',
        'asignado_gestor',
        'inicio_gestion',
        'fin_gestion',
        'creado_asignacion',
        'actualizado_asignacion',
    ];
    
    protected $casts = [
        'asignado_gestor' => 'datetime',
        'inicio_gestion' => 'datetime',
        'fin_gestion' => 'datetime',
        'creado_asignacion' => 'datetime',
        'actualizado_asignacion' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a una Propiedad
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }
    
    /**
     * Relación: pertenece a un Gestor
     */
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(UsuarioGestor::class, 'id_gestor_fk', 'id_usuario_gestor');
    }
    
    /**
     * Relación: pertenece a un Arrendador que hizo la asignación
     */
    public function arrendador(): BelongsTo
    {
        return $this->belongsTo(UsuarioArrendador::class, 'id_arrendador_asigno_fk', 'id_usuario_arrendador');
    }
}
