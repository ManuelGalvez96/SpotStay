<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsuarioGestor extends Model
{
    protected $table = 'tbl_usuario_gestor';
    protected $primaryKey = 'id_usuario_gestor';
    public $timestamps = false;
    
    protected $fillable = [
        'id_usuario_fk',
        'nif_gestor',
        'empresa_gestor',
        'descripcion_gestor',
        'especialidades_gestor',
        'propiedades_gestionadas',
        'calificacion_gestor',
        'tareas_completadas',
        'creado_gestor',
        'actualizado_gestor',
    ];
    
    protected $casts = [
        'calificacion_gestor' => 'decimal:2',
        'creado_gestor' => 'datetime',
        'actualizado_gestor' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a un Usuario base
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'id_usuario', 'id_usuario_fk');
    }
    
    /**
     * Relación: tiene muchos códigos de gestor
     */
    public function codigos(): HasMany
    {
        return $this->hasMany(CodigoGestor::class, 'id_gestor_fk', 'id_usuario_gestor');
    }
    
    /**
     * Relación: tiene muchas asignaciones
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionGestor::class, 'id_gestor_fk', 'id_usuario_gestor');
    }
}
