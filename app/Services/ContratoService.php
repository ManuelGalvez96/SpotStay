<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ContratoService
{
    /**
     * Obtener la URL del contrato para una propiedad comprobando permisos del usuario
     *
     * @param int $propiedadId
     * @param int $userId
     * @return string URL o ruta relativa al public
     * @throws \Exception si no existe o no tiene permisos
     */
    public function obtenerUrlContratoParaUsuario(int $propiedadId, int $userId): string
    {
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->leftJoin('tbl_contrato', 'tbl_contrato.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->where('tbl_alquiler.id_propiedad_fk', $propiedadId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(fn($q) => $q->where('tbl_alquiler.id_inquilino_fk', $userId)->orWhere('tbl_propiedad.id_arrendador_fk', $userId))
            ->select('tbl_alquiler.*', 'tbl_contrato.url_pdf_contrato')
            ->first();

        if (!$alquiler) {
            throw new \Exception('No tiene permiso para descargar este contrato o no existe el alquiler.');
        }

        if (empty($alquiler->url_pdf_contrato)) {
            throw new \Exception('Contrato no disponible para descarga.');
        }
        $url = $alquiler->url_pdf_contrato;

        // Si es URL externa, aceptamos
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        // Normalizar ruta relativa (quitar slash inicial si existe)
        $rutaRel = ltrim($url, '/');
        $rutaPublic = public_path($rutaRel);

        if (!file_exists($rutaPublic)) {
            throw new \Exception('Fichero de contrato no encontrado en el servidor.');
        }

        return $rutaRel;
    }

    /**
     * Obtener información del contrato (url, si es externa y si existe)
     * Retorna array: ['url' => string|null, 'is_external' => bool, 'exists' => bool]
     */
    public function obtenerInfoContratoParaUsuario(int $propiedadId, int $userId): array
    {
        $alquiler = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->leftJoin('tbl_contrato', 'tbl_contrato.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->where('tbl_alquiler.id_propiedad_fk', $propiedadId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where(fn($q) => $q->where('tbl_alquiler.id_inquilino_fk', $userId)->orWhere('tbl_propiedad.id_arrendador_fk', $userId))
            ->select('tbl_contrato.url_pdf_contrato')
            ->first();

        if (!$alquiler) {
            return ['url' => null, 'is_external' => false, 'exists' => false];
        }

        $url = $alquiler->url_pdf_contrato;
        if (empty($url)) {
            return ['url' => null, 'is_external' => false, 'exists' => false];
        }

        if (preg_match('#^https?://#i', $url)) {
            return ['url' => $url, 'is_external' => true, 'exists' => true];
        }

        $rutaRel = ltrim($url, '/');
        $rutaPublic = public_path($rutaRel);
        $exists = file_exists($rutaPublic);

        return ['url' => $rutaRel, 'is_external' => false, 'exists' => $exists];
    }
}
