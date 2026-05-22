<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudGestor extends Model
{
    protected $table = 'tbl_solicitud_gestor';
    protected $primaryKey = 'id_solicitud_gestor';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'id_admin_revisa_fk',
        'descripcion_solicitud',
        'experiencia_solicitud',
        'acepta_terminos_solicitud',
        'acepta_veracidad_solicitud',
        'fecha_aceptacion_solicitud',
        'estado_solicitud_gestor',
        'notas_solicitud_gestor',
        'creado_solicitud_gestor',
        'actualizado_solicitud_gestor',
    ];

    protected $casts = [
        'acepta_terminos_solicitud' => 'boolean',
        'acepta_veracidad_solicitud' => 'boolean',
        'fecha_aceptacion_solicitud' => 'date',
        'creado_solicitud_gestor' => 'datetime',
        'actualizado_solicitud_gestor' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }

    public function adminRevisa(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_admin_revisa_fk', 'id_usuario');
    }
}
