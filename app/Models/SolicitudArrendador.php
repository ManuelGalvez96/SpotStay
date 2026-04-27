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
        'telefono_solicitud',
        'fecha_nacimiento_solicitud',
        'tipo_documento_solicitud',
        'numero_documento_solicitud',
        'iban_solicitud',
        'titular_cuenta_solicitud',
        'nif_solicitud',
        'direccion_fiscal_solicitud',
        'tipo_arrendador_solicitud',
        'descripcion_solicitud',
        'num_propiedades_previstas_solicitud',
        'es_propietario_solicitud',
        'acepta_terminos_solicitud',
        'acepta_veracidad_solicitud',
        'fecha_aceptacion_solicitud',
        'estado_solicitud_arrendador',
        'notas_solicitud_arrendador',
        'creado_solicitud_arrendador',
        'actualizado_solicitud_arrendador',
    ];

    protected $casts = [
        'fecha_nacimiento_solicitud' => 'date',
        'es_propietario_solicitud' => 'boolean',
        'acepta_terminos_solicitud' => 'boolean',
        'acepta_veracidad_solicitud' => 'boolean',
        'fecha_aceptacion_solicitud' => 'date',
        'num_propiedades_previstas_solicitud' => 'integer',
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
