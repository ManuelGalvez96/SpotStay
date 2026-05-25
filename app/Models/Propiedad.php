<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Propiedad
 * 
 * Tabla de propiedades en alquiler - registra todos los inmuebles disponibles en la plataforma
 */
class Propiedad extends Model
{
    protected $table = 'tbl_propiedad';
    protected $primaryKey = 'id_propiedad';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_arrendador_fk',
        'id_gestor_fk',
        'id_admin_aprueba_fk',
        'titulo_propiedad',
        'calle_propiedad',
        'numero_propiedad',
        'piso_propiedad',
        'puerta_propiedad',
        'ciudad_propiedad',
        'codigo_postal_propiedad',
        'latitud_propiedad',
        'longitud_propiedad',
        'descripcion_propiedad',
        'precio_propiedad',
        'banos_propiedad',
        'notas_admin_propiedad',
        'aprobada_propiedad',
        'tipo_propiedad',
        'habitaciones_propiedad',
        'metros_cuadrados_propiedad',
        'estado_propiedad',
        'creado_propiedad',
        'actualizado_propiedad',
    ];

    protected $casts = [
        'latitud_propiedad' => 'decimal:7',
        'longitud_propiedad' => 'decimal:7',
        'precio_propiedad' => 'decimal:2',
        'banos_propiedad' => 'integer',
        'aprobada_propiedad' => 'boolean',
        'metros_cuadrados_propiedad' => 'integer',
        'creado_propiedad' => 'datetime',
        'actualizado_propiedad' => 'datetime',
    ];

    // Usuario arrendador propietario de la propiedad
    public function arrendador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_arrendador_fk', 'id_usuario');
    }

    // Usuario gestor responsable de la propiedad
    public function gestor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_gestor_fk', 'id_usuario');
    }

    // Alquileres activos o históricos de esta propiedad
    public function alquileres(): HasMany
    {
        return $this->hasMany(Alquiler::class, 'id_propiedad_fk', 'id_propiedad');
    }

    // Fotos asociadas a la propiedad
    public function fotos(): HasMany
    {
        return $this->hasMany(Foto::class, 'id_propiedad_fk', 'id_propiedad')
            ->orderBy('es_principal_foto', 'desc');
    }

    // Incidencias reportadas en esta propiedad
    public function incidencias(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'id_propiedad_fk', 'id_propiedad');
    }

    // Conversaciones asociadas a esta propiedad
    public function conversaciones(): HasMany
    {
        return $this->hasMany(Conversacion::class, 'id_propiedad_fk', 'id_propiedad');
    }

    /**
     * Reconstruye la dirección completa combinando calle, número, piso y puerta.
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = [$this->calle_propiedad, $this->numero_propiedad];
        if ($this->piso_propiedad) $partes[] = "Piso " . $this->piso_propiedad;
        if ($this->puerta_propiedad) $partes[] = "Pta " . $this->puerta_propiedad;
        
        return implode(', ', array_filter($partes));
    }
}
