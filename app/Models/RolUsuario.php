<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RolUsuario
 * 
 * Tabla pivote - asocia roles a usuarios (relación muchos a muchos)
 */
class RolUsuario extends Model
{
    protected $table = 'tbl_rol_usuario';
    protected $primaryKey = 'id_rol_usuario';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'id_rol_fk',
        'asignado_rol_usuario',
    ];

    protected $casts = [
        'asignado_rol_usuario' => 'datetime',
    ];

    // Usuario al que se asigna el rol
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }

    // Rol que se asigna al usuario
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol_fk', 'id_rol');
    }
}
