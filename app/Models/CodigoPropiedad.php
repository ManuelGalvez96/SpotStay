<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodigoPropiedad extends Model
{
    protected $table = 'tbl_codigo_propiedad';
    protected $primaryKey = 'id_codigo_propiedad';
    public $timestamps = false;
    
    protected $fillable = [
        'codigo_propiedad',
        'id_propiedad_fk',
        'id_arrendador_genero_fk',
        'estado_codigo_propiedad',
        'dias_validez_codigo',
        'expira_codigo_propiedad',
        'id_inquilino_uso_fk',
        'usado_codigo_propiedad',
        'usos_codigo',
        'creado_codigo_propiedad',
        'actualizado_codigo_propiedad',
    ];
    
    protected $casts = [
        'expira_codigo_propiedad' => 'datetime',
        'usado_codigo_propiedad' => 'datetime',
        'creado_codigo_propiedad' => 'datetime',
        'actualizado_codigo_propiedad' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a una Propiedad
     */
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }
    
    /**
     * Relación: pertenece a un Arrendador
     */
    public function arrendador(): BelongsTo
    {
        return $this->belongsTo(UsuarioArrendador::class, 'id_arrendador_genero_fk', 'id_usuario_arrendador');
    }
    
    /**
     * Relación: pertenece a un Inquilino (nullable)
     */
    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(UsuarioInquilino::class, 'id_inquilino_uso_fk', 'id_usuario_inquilino');
    }
    
    /**
     * Verifica si un código ha expirado
     */
    public function esValido(): bool
    {
        return $this->estado_codigo_propiedad === 'activo' 
            && now()->lessThan($this->expira_codigo_propiedad);
    }
}
