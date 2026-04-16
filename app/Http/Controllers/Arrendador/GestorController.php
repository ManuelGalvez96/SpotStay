<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GestorController extends Controller
{
    public function index(Request $request): View
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = DB::table('tbl_usuario')
            ->where('id_usuario', $arrendadorId)
            ->select('id_usuario', 'nombre_usuario', 'email_usuario')
            ->first();

        $propiedades = DB::table('tbl_propiedad as p')
            ->leftJoin('tbl_usuario as gestor', 'gestor.id_usuario', '=', 'p.id_gestor_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'p.id_propiedad',
                'p.titulo_propiedad',
                'p.direccion_propiedad',
                'p.ciudad_propiedad',
                'p.estado_propiedad',
                'p.id_gestor_fk',
                'gestor.nombre_usuario as nombre_gestor',
                'gestor.email_usuario as email_gestor'
            )
            ->orderByDesc('p.creado_propiedad')
            ->paginate(10);

        $gestoresDisponibles = $this->obtenerGestoresDisponibles($arrendadorId, $arrendador?->nombre_usuario, $arrendador?->email_usuario);

        $totalPropiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->count();

        $conGestorExterno = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $arrendadorId)
            ->where('id_gestor_fk', '!=', $arrendadorId)
            ->count();

        return view('arrendador.gestor', [
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
            'propiedades' => $propiedades,
            'gestoresDisponibles' => $gestoresDisponibles,
            'totalPropiedades' => $totalPropiedades,
            'conGestorExterno' => $conGestorExterno,
        ]);
    }

    public function actualizar(Request $request, int $id): JsonResponse
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $datos = $request->validate([
            'id_gestor_fk' => ['required', 'integer', 'min:1'],
        ]);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->where('id_arrendador_fk', $arrendadorId)
            ->first();

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro la propiedad.',
            ], 404);
        }

        $gestorValido = DB::table('tbl_usuario')
            ->where('id_usuario', (int) $datos['id_gestor_fk'])
            ->where('activo_usuario', true)
            ->exists();

        if (!$gestorValido) {
            return response()->json([
                'success' => false,
                'message' => 'El gestor seleccionado no es valido.',
            ], 422);
        }

        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update([
                'id_gestor_fk' => (int) $datos['id_gestor_fk'],
                'actualizado_propiedad' => Carbon::now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Gestor actualizado correctamente.',
        ]);
    }

    private function obtenerIdArrendador(Request $request): int
    {
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

    private function obtenerGestoresDisponibles(int $arrendadorId, ?string $nombreArrendador, ?string $emailArrendador): Collection
    {
        $rolGestorId = DB::table('tbl_rol')
            ->where('slug_rol', 'gestor')
            ->value('id_rol');

        $gestores = collect();

        if ($rolGestorId) {
            $gestores = DB::table('tbl_rol_usuario as ru')
                ->join('tbl_usuario as u', 'u.id_usuario', '=', 'ru.id_usuario_fk')
                ->where('ru.id_rol_fk', $rolGestorId)
                ->where('u.activo_usuario', true)
                ->select('u.id_usuario', 'u.nombre_usuario', 'u.email_usuario')
                ->orderBy('u.nombre_usuario')
                ->get();
        }

        if ($gestores->isEmpty()) {
            $gestores = DB::table('tbl_usuario')
                ->where('activo_usuario', true)
                ->select('id_usuario', 'nombre_usuario', 'email_usuario')
                ->orderBy('nombre_usuario')
                ->limit(30)
                ->get();
        }

        if (!$gestores->firstWhere('id_usuario', $arrendadorId)) {
            $gestores->prepend((object) [
                'id_usuario' => $arrendadorId,
                'nombre_usuario' => $nombreArrendador ?: 'Arrendador',
                'email_usuario' => $emailArrendador,
            ]);
        }

        return $gestores->unique('id_usuario')->values();
    }

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
