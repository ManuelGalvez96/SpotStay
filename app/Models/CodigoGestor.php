<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CodigoGestor extends Model
{
    protected $table = 'tbl_codigo_gestor';
    protected $primaryKey = 'id_codigo_gestor';
    public $timestamps = false;
    
    protected $fillable = [
        'codigo_gestor',
        'id_gestor_fk',
        'estado_codigo_gestor',
        'creado_codigo_gestor',
        'actualizado_codigo_gestor',
        'cancelado_codigo_gestor',
    ];
    
    protected $casts = [
        'creado_codigo_gestor' => 'datetime',
        'actualizado_codigo_gestor' => 'datetime',
        'cancelado_codigo_gestor' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a un Gestor
     */
    public function gestor(): HasOne
    {
        return $this->hasOne(UsuarioGestor::class, 'id_usuario_gestor', 'id_gestor_fk');
    }
}
