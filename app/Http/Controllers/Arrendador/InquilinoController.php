<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;


class InquilinoController extends Controller
{
    public function inicio(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = DB::table('tbl_usuario')
            ->select('id_usuario', 'nombre_usuario')
            ->where('id_usuario', $arrendadorId)
            ->first();

        $inquilinos = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->join('tbl_usuario as u', 'u.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('a.estado_alquiler', 'activo')
            ->select(
                'u.id_usuario',
                'u.nombre_usuario',
                'u.email_usuario',
                'u.telefono_usuario',
                DB::raw('COUNT(DISTINCT p.id_propiedad) as total_propiedades'),
                DB::raw('MAX(a.fecha_inicio_alquiler) as fecha_inicio_reciente')
            )
            ->groupBy('u.id_usuario', 'u.nombre_usuario', 'u.email_usuario', 'u.telefono_usuario')
            ->orderBy('u.nombre_usuario')
            ->paginate(10);

        $totales = [
            'inquilinos' => DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->distinct()
                ->count('a.id_inquilino_fk'),
            'alquileres_activos' => DB::table('tbl_alquiler as a')
                ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->count(),
            'propiedades_ocupadas' => DB::table('tbl_propiedad as p')
                ->join('tbl_alquiler as a', 'a.id_propiedad_fk', '=', 'p.id_propiedad')
                ->where('p.id_arrendador_fk', $arrendadorId)
                ->where('a.estado_alquiler', 'activo')
                ->distinct()
                ->count('p.id_propiedad'),
        ];

        return view('arrendador.inquilinos', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'inquilinos' => $inquilinos,
            'totales' => $totales,
        ]);
    }

    public function mostrar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $inquilino = DB::table('tbl_usuario')
            ->select('id_usuario', 'nombre_usuario', 'email_usuario', 'telefono_usuario', 'creado_usuario')
            ->where('id_usuario', $id)
            ->first();

        if (!$inquilino) {
            return response()->json(['success' => false, 'message' => 'Inquilino no encontrado.'], 404);
        }

        $propiedades = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('a.id_inquilino_fk', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('a.estado_alquiler', 'activo')
            ->select(
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'a.fecha_inicio_alquiler',
                'a.fecha_fin_alquiler'
            )
            ->orderByDesc('a.fecha_inicio_alquiler')
            ->get();

        return response()->json([
            'success' => true,
            'inquilino' => $inquilino,
            'propiedades' => $propiedades,
        ]);
    }

    private function obtenerIdArrendador(Request $request): int
    {
        if (Auth::check()) {
            $usuarioAutenticado = Auth::user();
            if ($usuarioAutenticado && DB::table('tbl_rol_usuario as ru')
                ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
                ->where('ru.id_usuario_fk', $usuarioAutenticado->id_usuario)
                ->where('r.slug_rol', 'arrendador')
                ->exists()
            ) {
                return (int) $usuarioAutenticado->id_usuario;
            }
        }

        $arrendadorId = (int) $request->query('arrendador_id', 0);

        if ($arrendadorId > 0) {
            return $arrendadorId;
        }

        return (int) DB::table('tbl_usuario as u')
            ->join('tbl_propiedad as p', 'p.id_arrendador_fk', '=', 'u.id_usuario')
            ->where('u.activo_usuario', true)
            ->groupBy('u.id_usuario')
            ->select('u.id_usuario', DB::raw('COUNT(*) as total_propiedades'))
            ->orderByDesc('total_propiedades')
            ->orderBy('u.id_usuario')
            ->value('u.id_usuario');
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }

    private function obtenerSelectDireccionPropiedad(string $aliasTabla = 'p'): string
    {
        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            return "{$aliasTabla}.direccion_propiedad as direccion_propiedad";
        }

        $partes = [];
        foreach (['calle_propiedad', 'numero_propiedad', 'piso_propiedad', 'puerta_propiedad'] as $columna) {
            if (Schema::hasColumn('tbl_propiedad', $columna)) {
                $partes[] = "NULLIF(TRIM({$aliasTabla}.{$columna}), '')";
            }
        }

        if (empty($partes)) {
            return "'' as direccion_propiedad";
        }

        return 'TRIM(CONCAT_WS(\' \' , ' . implode(', ', $partes) . ')) as direccion_propiedad';
    }
}
