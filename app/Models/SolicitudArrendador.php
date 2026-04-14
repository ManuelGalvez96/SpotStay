<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SolicitudArrendador
 * 
 * Tabla de solicitudes de arrendador - almacena solicitudes de usuarios para convertirse en arrendadores
 */
class SolicitudArrendador extends Model
{
    protected $table = 'tbl_solicitud_arrendador';
    protected $primaryKey = 'id_solicitud_arrendador';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'id_admin_revisa_fk',
        'datos_solicitud_arrendador',
        'estado_solicitud_arrendador',
        'notas_solicitud_arrendador',
        'creado_solicitud_arrendador',
        'actualizado_solicitud_arrendador',
    ];

    protected $casts = [
        'datos_solicitud_arrendador' => 'array',
        'creado_solicitud_arrendador' => 'datetime',
        'actualizado_solicitud_arrendador' => 'datetime',
    ];

    // Usuario que realiza la solicitud
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }

    // Admin que revisa la solicitud
    public function adminRevisa(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_admin_revisa_fk', 'id_usuario');
    }
}
