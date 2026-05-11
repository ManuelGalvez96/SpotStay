<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsuarioArrendador extends Model
{
    protected $table = 'tbl_usuario_arrendador';
    protected $primaryKey = 'id_usuario_arrendador';
    public $timestamps = false;
    
    protected $fillable = [
        'id_usuario_fk',
        'nif_arrendador',
        'iban_arrendador',
        'entidad_bancaria_arrendador',
        'titular_cuenta_arrendador',
        'verificado_identidad_arrendador',
        'certificado_arrendador_ruta',
        'verificado_certificado_arrendador',
        'tipo_arrendador',
        'nombre_empresa_arrendador',
        'cif_empresa_arrendador',
        'direccion_fiscal_arrendador',
        'codigo_postal_arrendador',
        'ciudad_arrendador',
        'provincia_arrendador',
        'propiedades_activas',
        'propiedades_alquiladas',
        'ingresos_totales',
        'calificacion_arrendador',
        'creado_arrendador',
        'actualizado_arrendador',
    ];
    
    protected $casts = [
        'verificado_identidad_arrendador' => 'boolean',
        'verificado_certificado_arrendador' => 'datetime',
        'ingresos_totales' => 'decimal:2',
        'calificacion_arrendador' => 'decimal:2',
        'creado_arrendador' => 'datetime',
        'actualizado_arrendador' => 'datetime',
    ];
    
    /**
     * Relación: pertenece a un Usuario base
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(Usuario::class, 'id_usuario', 'id_usuario_fk');
    }
    
    /**
     * Relación: tiene muchas asignaciones de gestores
     */
    public function asignacionesGestores(): HasMany
    {
        return $this->hasMany(AsignacionGestor::class, 'id_arrendador_asigno_fk', 'id_usuario_arrendador');
    }
    
    /**
     * Relación: tiene muchos códigos de propiedades
     */
    public function codigosPropiedades(): HasMany
    {
        return $this->hasMany(CodigoPropiedad::class, 'id_arrendador_genero_fk', 'id_usuario_arrendador');
    }
}
