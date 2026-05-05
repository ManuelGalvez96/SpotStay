<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SolicitudController extends Controller
{
    public function inicio(Request $request)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $arrendador = DB::table('tbl_usuario')
            ->select('id_usuario', 'nombre_usuario')
            ->where('id_usuario', $arrendadorId)
            ->first();

        $solicitudes = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'a.id_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'a.estado_alquiler',
                'a.fecha_inicio_alquiler',
                'a.fecha_fin_alquiler',
                'a.creado_alquiler'
            )
            ->orderByDesc('a.creado_alquiler')
            ->paginate(10);

        $total = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->count();

        $pendientes = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('a.estado_alquiler', 'pendiente')
            ->count();

        $activos = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('a.estado_alquiler', 'activo')
            ->count();

        $rechazados = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('a.estado_alquiler', 'rechazado')
            ->count();

        return view('arrendador.solicitudes', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'solicitudes' => $solicitudes,
            'totales' => [
                'total' => $total,
                'pendientes' => $pendientes,
                'activos' => $activos,
                'rechazados' => $rechazados,
            ],
        ]);
    }

    public function aprobar(Request $request, int $id)
    {
        return $this->cambiarEstado($request, $id, 'activo');
    }

    public function rechazar(Request $request, int $id)
    {
        return $this->cambiarEstado($request, $id, 'rechazado');
    }

    public function ver(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $solicitud = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'a.id_inquilino_fk')
            ->where('a.id_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                'a.id_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                'a.estado_alquiler',
                'a.fecha_inicio_alquiler',
                'a.fecha_fin_alquiler',
                'p.precio_propiedad as precio_alquiler',
                'a.creado_alquiler'
            )
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $solicitud,
        ]);
    }

    public function actualizar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $alquiler = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('a.id_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('a.id_alquiler', 'a.estado_alquiler')
            ->first();

        if (!$alquiler) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        if ($alquiler->estado_alquiler === 'activo') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar una solicitud con estado activo.',
            ], 403);
        }

        $datosActualizar = [];

        if ($request->has('fecha_inicio_alquiler')) {
            $datosActualizar['fecha_inicio_alquiler'] = $request->input('fecha_inicio_alquiler');
        }

        if ($request->has('fecha_fin_alquiler')) {
            $datosActualizar['fecha_fin_alquiler'] = $request->input('fecha_fin_alquiler');
        }

        if (empty($datosActualizar)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos para actualizar.',
            ], 400);
        }

        $datosActualizar['actualizado_alquiler'] = Carbon::now();

        DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->update($datosActualizar);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente.',
        ]);
    }

    public function eliminar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $alquiler = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('a.id_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('a.id_alquiler')
            ->first();

        if (!$alquiler) {
            \Log::info('Solicitud no encontrada', ['id' => $id, 'arrendadorId' => $arrendadorId]);
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // Eliminar contratos relacionados
            DB::table('tbl_contrato')
                ->where('id_alquiler_fk', $id)
                ->delete();

            // Eliminar el alquiler
            DB::table('tbl_alquiler')
                ->where('id_alquiler', $id)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud eliminada correctamente.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al eliminar solicitud', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function cambiarEstado(Request $request, int $id, string $estado)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $alquiler = DB::table('tbl_alquiler as a')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 'a.id_propiedad_fk')
            ->where('a.id_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('a.id_alquiler')
            ->first();

        if (!$alquiler) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        $datosEstado = [
            'estado_alquiler' => $estado,
            'actualizado_alquiler' => Carbon::now(),
        ];

        if ($estado === 'activo') {
            $datosEstado['aprobado_alquiler'] = Carbon::now();
        }

        DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->update($datosEstado);

        return response()->json([
            'success' => true,
            'message' => $estado === 'activo' ? 'Solicitud aprobada.' : 'Solicitud rechazada.',
            'estado' => $estado,
        ]);
    }

    private function obtenerIdArrendador(Request $request): int
    {
        $arrendadorId = (int) $request->query('arrendador_id', $request->input('arrendador_id', 0));

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
