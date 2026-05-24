<?php

namespace App\Services;

use App\Models\CodigoGestor;
use Carbon\Carbon;

class CodigoGestorService
{
    /**
     * Genera un código único de gestor en formato GES-XXXX-XXXX
     */
    public static function generarCodigo(): string
    {
        do {
            $codigo = 'GES-' . 
                      strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4)) . '-' .
                      strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
        } while (CodigoGestor::where('codigo_gestor', $codigo)->exists());
        
        return $codigo;
    }
    
    /**
     * Crea un nuevo código para un gestor
     */
    public static function crearCodigoParaGestor(int $idGestor): CodigoGestor
    {
        $codigo = self::generarCodigo();
        
        return CodigoGestor::create([
            'codigo_gestor' => $codigo,
            'id_gestor_fk' => $idGestor,
            'estado_codigo_gestor' => 'activo',
        ]);
    }

    /**
     * Obtiene el código activo del gestor o crea uno nuevo si no existe.
     */
    public static function obtenerOCrearCodigoParaGestor(int $idGestor): CodigoGestor
    {
        $codigoExistente = CodigoGestor::where('id_gestor_fk', $idGestor)
            ->where('estado_codigo_gestor', 'activo')
            ->first();

        if ($codigoExistente) {
            return $codigoExistente;
        }

        return self::crearCodigoParaGestor($idGestor);
    }
    
    /**
     * Busca un gestor por código válido
     */
    public static function obtenerGestorPorCodigo(string $codigo)
    {
        $codigoGestor = CodigoGestor::where('codigo_gestor', $codigo)
            ->where('estado_codigo_gestor', 'activo')
            ->first();
        
        if (!$codigoGestor) {
            return null;
        }
        
        return $codigoGestor->gestor;
    }
    
    /**
     * Valida si un código de gestor es válido
     */
    public static function validarCodigo(string $codigo): bool
    {
        return CodigoGestor::where('codigo_gestor', $codigo)
            ->where('estado_codigo_gestor', 'activo')
            ->exists();
    }
    
    /**
     * Cancela un código de gestor
     */
    public static function cancelarCodigo(string $codigo): bool
    {
        $codigoGestor = CodigoGestor::where('codigo_gestor', $codigo)->first();
        
        if (!$codigoGestor) {
            return false;
        }
        
        return $codigoGestor->update([
            'estado_codigo_gestor' => 'cancelado',
            'cancelado_codigo_gestor' => now(),
        ]);
    }
}
