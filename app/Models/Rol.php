<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Rol
 * 
 * Tabla de roles del sistema - define los diferentes tipos de usuarios y sus permisos
 * (administrador, arrendador, inquilino, gestor, miembro)
 */
class Rol extends Model
{
    protected $table = 'tbl_rol';
    protected $primaryKey = 'id_rol';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_rol',
        'slug_rol',
        'creado_rol',
        'actualizado_rol',
    ];

    protected $casts = [
        'creado_rol' => 'datetime',
        'actualizado_rol' => 'datetime',
    ];

    // Usuarios que tienen este rol asignado (relación many-to-many)
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'tbl_rol_usuario',
            'id_rol_fk',
            'id_usuario_fk',
            'id_rol',
            'id_usuario'
        );
    }
}

