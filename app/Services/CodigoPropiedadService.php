<?php

namespace App\Services;

use App\Models\CodigoPropiedad;
use Carbon\Carbon;

class CodigoPropiedadService
{
    /**
     * Genera un código único de propiedad en formato PROP-XXXX-XXXX
     */
    public static function generarCodigo(): string
    {
        do {
            $codigo = 'PROP-' . 
                      strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4)) . '-' .
                      strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        } while (CodigoPropiedad::where('codigo_propiedad', $codigo)->exists());
        
        return $codigo;
    }
    
    /**
     * Crea un nuevo código para una propiedad
     */
    public static function crearCodigoParaPropiedad(int $idPropiedad, int $idArrendador, int $diasValidez = 30): CodigoPropiedad
    {
        $codigo = self::generarCodigo();
        $expira = now()->addDays($diasValidez);
        
        return CodigoPropiedad::create([
            'codigo_propiedad' => $codigo,
            'id_propiedad_fk' => $idPropiedad,
            'id_arrendador_genero_fk' => $idArrendador,
            'estado_codigo_propiedad' => 'activo',
            'dias_validez_codigo' => $diasValidez,
            'expira_codigo_propiedad' => $expira,
        ]);
    }
    
    /**
     * Valida si un código es válido (existe, está activo y no ha expirado)
     */
    public static function validarCodigo(string $codigo): bool
    {
        $codigoPropiedad = CodigoPropiedad::where('codigo_propiedad', $codigo)->first();
        
        if (!$codigoPropiedad) {
            return false;
        }
        
        // Verificar que esté activo
        if ($codigoPropiedad->estado_codigo_propiedad !== 'activo') {
            return false;
        }
        
        // Verificar que no haya expirado
        if (now()->greaterThan($codigoPropiedad->expira_codigo_propiedad)) {
            // Marcar como expirado
            $codigoPropiedad->update(['estado_codigo_propiedad' => 'expirado']);
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtiene la propiedad asociada a un código válido
     */
    public static function obtenerPropiedadPorCodigo(string $codigo)
    {
        if (!self::validarCodigo($codigo)) {
            return null;
        }
        
        $codigoPropiedad = CodigoPropiedad::where('codigo_propiedad', $codigo)->first();
        return $codigoPropiedad ? $codigoPropiedad->propiedad : null;
    }
    
    /**
     * Registra el uso de un código (cuando un inquilino lo utiliza)
     */
    public static function registrarUso(string $codigo, int $idInquilino): bool
    {
        $codigoPropiedad = CodigoPropiedad::where('codigo_propiedad', $codigo)->first();
        
        if (!$codigoPropiedad || !self::validarCodigo($codigo)) {
            return false;
        }
        
        return $codigoPropiedad->update([
            'id_inquilino_uso_fk' => $idInquilino,
            'usado_codigo_propiedad' => now(),
            'estado_codigo_propiedad' => 'usado',
            'usos_codigo' => $codigoPropiedad->usos_codigo + 1,
        ]);
    }
    
    /**
     * Incrementa el contador de intentos de uso
     */
    public static function incrementarIntentos(string $codigo): bool
    {
        $codigoPropiedad = CodigoPropiedad::where('codigo_propiedad', $codigo)->first();
        
        if (!$codigoPropiedad) {
            return false;
        }
        
        return $codigoPropiedad->increment('usos_codigo');
    }
    
    /**
     * Cancela un código (el arrendador puede cancelarlo)
     */
    public static function cancelarCodigo(string $codigo): bool
    {
        $codigoPropiedad = CodigoPropiedad::where('codigo_propiedad', $codigo)->first();
        
        if (!$codigoPropiedad) {
            return false;
        }
        
        return $codigoPropiedad->update([
            'estado_codigo_propiedad' => 'cancelado',
        ]);
    }
    
    /**
     * Obtiene todos los códigos activos de una propiedad
     */
    public static function obtenerCodigosActivosDePropiedad(int $idPropiedad): \Illuminate\Database\Eloquent\Collection
    {
        return CodigoPropiedad::where('id_propiedad_fk', $idPropiedad)
            ->where('estado_codigo_propiedad', 'activo')
            ->where('expira_codigo_propiedad', '>', now())
            ->get();
    }
}
