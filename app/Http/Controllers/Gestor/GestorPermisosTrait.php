<?php

namespace App\Http\Controllers\Gestor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

trait GestorPermisosTrait
{
    private function getPermisosPropiedad(int $gestorId, int $propiedadId): ?object
    {
        $permisos = DB::table('tbl_propiedad_permisos')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('id_gestor_fk', $gestorId)
            ->first();

        if (!$permisos) {
            return (object) [
                'incidencias' => false,
                'gastos' => false,
                'chat' => false,
                'editar_propiedad' => false,
            ];
        }

        return $permisos;
    }

    private function getPropiedadesConPermiso(int $gestorId, string $permiso): array
    {
        return DB::table('tbl_propiedad_permisos')
            ->where('id_gestor_fk', $gestorId)
            ->where($permiso, true)
            ->pluck('id_propiedad_fk')
            ->all();
    }

    private function getPermisosMultiplesPropiedades(int $gestorId, array $propiedadIds): array
    {
        if (empty($propiedadIds)) {
            return [];
        }

        $rows = DB::table('tbl_propiedad_permisos')
            ->where('id_gestor_fk', $gestorId)
            ->whereIn('id_propiedad_fk', $propiedadIds)
            ->get()
            ->keyBy('id_propiedad_fk');

        $resultado = [];
        foreach ($propiedadIds as $pid) {
            if (isset($rows[$pid])) {
                $resultado[$pid] = (object) [
                    'incidencias' => (bool) $rows[$pid]->incidencias,
                    'gastos' => (bool) $rows[$pid]->gastos,
                    'chat' => (bool) $rows[$pid]->chat,
                    'editar_propiedad' => (bool) $rows[$pid]->editar_propiedad,
                ];
            } else {
                $resultado[$pid] = (object) [
                    'incidencias' => false,
                    'gastos' => false,
                    'chat' => false,
                    'editar_propiedad' => false,
                ];
            }
        }

        return $resultado;
    }

    private function redirigirSinPermiso(string $accion): mixed
    {
        $mensajes = [
            'incidencias' => 'No tienes permiso para gestionar incidencias en esta propiedad.',
            'gastos' => 'No tienes permiso para gestionar gastos en esta propiedad.',
            'chat' => 'No tienes permiso para acceder al chat de esta propiedad.',
            'editar_propiedad' => 'No tienes permiso para editar esta propiedad.',
        ];

        $mensaje = $mensajes[$accion] ?? 'No tienes permisos suficientes para realizar esta acción.';

        return Redirect::back()->with('error', $mensaje);
    }
}
