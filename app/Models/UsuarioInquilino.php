<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsuarioInquilino extends Model
{
    protected $table = 'tbl_usuario_inquilino';
    protected $primaryKey = 'id_usuario_inquilino';
    public $timestamps = false;
    
    protected $fillable = [
        'id_usuario_fk',
        'nif_inquilino',
        'documento_identidad_inquilino_ruta',
        'verificado_identidad_inquilino',
        'verificado_identidad_inquilino_fecha',
        'empresa_inquilino',
        'puesto_inquilino',
        'tipo_inquilino',
        'referencias_inquilino',
        'puntuacion_inquilino',
        'propiedades_alquiladas',
        'preferencias_ubicacion',
        'presupuesto_maximo_inquilino',
        'preferencias_adicionales_inquilino',
        'verificado_perfil_inquilino',
        'incidencias_abiertas',
        'creado_inquilino',
        'actualizado_inquilino',
    ];
    
    protected $casts = [
        'verificado_identidad_inquilino' => 'boolean',
        'verificado_identidad_inquilino_fecha' => 'datetime',
        'verificado_perfil_inquilino' => 'boolean',
        'creado_inquilino' => 'datetime',
        'actualizado_inquilino' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a un Usuario base
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'id_usuario', 'id_usuario_fk');
    }
}
