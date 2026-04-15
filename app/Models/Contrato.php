<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Contrato
 * 
 * Tabla de contratos - almacena contratos digitales firmados entre arrendador e inquilino
 */
class Contrato extends Model
{
    protected $table = 'tbl_contrato';
    protected $primaryKey = 'id_contrato';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_alquiler_fk',
        'url_pdf_contrato',
        'hash_contrato',
        'firmado_arrendador',
        'fecha_firma_arrendador',
        'ip_firma_arrendador',
        'firmado_inquilino',
        'fecha_firma_inquilino',
        'ip_firma_inquilino',
        'estado_contrato',
        'creado_contrato',
        'actualizado_contrato',
    ];

    protected $casts = [
        'firmado_arrendador' => 'boolean',
        'fecha_firma_arrendador' => 'datetime',
        'firmado_inquilino' => 'boolean',
        'fecha_firma_inquilino' => 'datetime',
        'creado_contrato' => 'datetime',
        'actualizado_contrato' => 'datetime',
    ];

    // Alquiler asociado a este contrato
    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler_fk', 'id_alquiler');
    }
}
