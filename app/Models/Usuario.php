<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Usuario
 * 
 * Tabla de usuarios del sistema - registra todos los usuarios que acceden a la plataforma
 * (administradores, arrendadores, inquilinos, gestores, etc.)
 */
class Usuario extends Model
{
    protected $table = 'tbl_usuario';
    protected $primaryKey = 'id_usuario';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nombre_usuario',
        'email_usuario',
        'contrasena_usuario',
        'telefono_usuario',
        'avatar_usuario',
        'activo_usuario',
        'verificado_usuario',
        'token_usuario',
        'creado_usuario',
        'actualizado_usuario',
    ];

    protected $hidden = [
        'contrasena_usuario',
        'token_usuario',
    ];

    protected $casts = [
        'activo_usuario' => 'boolean',
        'verificado_usuario' => 'datetime',
        'creado_usuario' => 'datetime',
        'actualizado_usuario' => 'datetime',
    ];

    // Roles asignados al usuario (relación many-to-many)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'tbl_rol_usuario',
            'id_usuario_fk',
            'id_rol_fk',
            'id_usuario',
            'id_rol'
        )->withPivot('asignado_rol_usuario');
    }

    // Propiedades arrendadas por este usuario
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'id_arrendador_fk', 'id_usuario');
    }

    // Propiedades gestionadas por este usuario
    public function propiedadesGestionadas(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'id_gestor_fk', 'id_usuario');
    }

    // Alquileres donde este usuario es inquilino
    public function alquileres(): HasMany
    {
        return $this->hasMany(Alquiler::class, 'id_inquilino_fk', 'id_usuario');
    }

    // Alquileres aprobados por este usuario (admin)
    public function alquileresAprobados(): HasMany
    {
        return $this->hasMany(Alquiler::class, 'id_admin_aprueba_fk', 'id_usuario');
    }

    // Incidencias reportadas por este usuario
    public function incidenciasReportadas(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'id_reporta_fk', 'id_usuario');
    }

    // Incidencias asignadas a este usuario
    public function incidenciasAsignadas(): HasMany
    {
        return $this->hasMany(Incidencia::class, 'id_asignado_fk', 'id_usuario');
    }

    // Históricos de incidencias que ha comentado/actualizado este usuario
    public function historialesIncidencia(): HasMany
    {
        return $this->hasMany(HistorialIncidencia::class, 'id_usuario_fk', 'id_usuario');
    }

    // Conversaciones en las que participa este usuario
    public function conversaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Conversacion::class,
            'tbl_conversacion_usuario',
            'id_usuario_fk',
            'id_conversacion_fk',
            'id_usuario',
            'id_conversacion'
        )->withPivot('ultima_lectura_conv_usuario');
    }

    // Mensajes enviados por este usuario
    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'id_remitente_fk', 'id_usuario');
    }

    // Notificaciones de este usuario (ordenadas por fecha descendente)
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario_fk', 'id_usuario')
            ->orderBy('creado_notificacion', 'desc');
    }

    // Solicitudes de arrendador realizadas por este usuario
    public function solicitudesArrendador(): HasMany
    {
        return $this->hasMany(SolicitudArrendador::class, 'id_usuario_fk', 'id_usuario');
    }

    // Solicitudes de arrendador revisadas por este usuario (admin)
    public function solicitudesRevisadas(): HasMany
    {
        return $this->hasMany(SolicitudArrendador::class, 'id_admin_revisa_fk', 'id_usuario');
    }

    // Suscripciones de este usuario
    public function suscripciones(): HasMany
    {
        return $this->hasMany(Suscripcion::class, 'id_usuario_fk', 'id_usuario');
    }

    // Sesiones de chatbot iniciadas por este usuario
    public function sesionsChatbot(): HasMany
    {
        return $this->hasMany(ChatbotSesion::class, 'id_usuario_fk', 'id_usuario');
    }

    // Documentos uploadados por este usuario
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'id_usuario_fk', 'id_usuario');
    }

    // Pagos realizados por este usuario
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_pagador_fk', 'id_usuario');
    }
}
