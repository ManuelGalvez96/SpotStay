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

        $solicitudes = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 's.id_usuario_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                's.id_solicitud_alquiler as id_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                's.estado_solicitud_alquiler as estado_alquiler',
                's.fecha_inicio_solicitud_alquiler as fecha_inicio_alquiler',
                DB::raw('NULL as fecha_fin_alquiler'),
                's.creado_solicitud_alquiler as creado_alquiler'
            )
            ->orderByDesc('s.creado_solicitud_alquiler')
            ->paginate(10);

        $total = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->count();

        $pendientes = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('s.estado_solicitud_alquiler', 'pendiente')
            ->count();

        $activos = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->whereIn('s.estado_solicitud_alquiler', ['activo', 'aprobada'])
            ->count();

        $rechazados = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->whereIn('s.estado_solicitud_alquiler', ['rechazado', 'rechazada'])
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

        $solicitud = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 's.id_usuario_fk')
            ->where('s.id_solicitud_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                's.id_solicitud_alquiler as id_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                's.estado_solicitud_alquiler as estado_alquiler',
                's.fecha_inicio_solicitud_alquiler as fecha_inicio_alquiler',
                DB::raw('NULL as fecha_fin_alquiler'),
                'p.precio_propiedad as precio_alquiler',
                's.creado_solicitud_alquiler as creado_alquiler'
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

        $solicitud = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('s.id_solicitud_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('s.id_solicitud_alquiler', 's.estado_solicitud_alquiler')
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        if ($solicitud->estado_solicitud_alquiler === 'activo') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede editar una solicitud con estado activo.',
            ], 403);
        }

        $datosActualizar = [];

        if ($request->has('fecha_inicio_alquiler')) {
            $datosActualizar['fecha_inicio_solicitud_alquiler'] = $request->input('fecha_inicio_alquiler');
        }

        if (empty($datosActualizar)) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos para actualizar.',
            ], 400);
        }

        $datosActualizar['actualizado_solicitud_alquiler'] = Carbon::now();

        DB::table('tbl_solicitud_alquiler')
            ->where('id_solicitud_alquiler', $id)
            ->update($datosActualizar);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud actualizada correctamente.',
        ]);
    }

    public function eliminar(Request $request, int $id)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $solicitud = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('s.id_solicitud_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select('s.id_solicitud_alquiler')
            ->first();

        if (!$solicitud) {
            \Log::info('Solicitud no encontrada', ['id' => $id, 'arrendadorId' => $arrendadorId]);
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        DB::table('tbl_solicitud_alquiler')
            ->where('id_solicitud_alquiler', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud eliminada correctamente.',
        ]);
    }

    private function cambiarEstado(Request $request, int $id, string $estado)
    {
        $arrendadorId = $this->obtenerIdArrendador($request);

        $solicitud = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('s.id_solicitud_alquiler', $id)
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->select(
                's.id_solicitud_alquiler',
                's.id_propiedad_fk',
                's.id_usuario_fk',
                's.fecha_inicio_solicitud_alquiler',
                'p.precio_propiedad'
            )
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // 1. Actualizar el estado de la solicitud en tbl_solicitud_alquiler
            DB::table('tbl_solicitud_alquiler')
                ->where('id_solicitud_alquiler', $id)
                ->update([
                    'estado_solicitud_alquiler' => $estado,
                    'actualizado_solicitud_alquiler' => Carbon::now(),
                ]);

            // 2. Si se aprueba (estado === 'activo'), crear el alquiler real y el contrato
            if ($estado === 'activo') {
                $alquilerId = DB::table('tbl_alquiler')->insertGetId([
                    'id_propiedad_fk' => $solicitud->id_propiedad_fk,
                    'id_inquilino_fk' => $solicitud->id_usuario_fk,
                    'fecha_inicio_alquiler' => $solicitud->fecha_inicio_solicitud_alquiler,
                    'fecha_fin_alquiler' => null,
                    'precio_alquiler' => $solicitud->precio_propiedad,
                    'estado_alquiler' => 'activo',
                    'aprobado_alquiler' => Carbon::now(),
                    'creado_alquiler' => Carbon::now(),
                    'actualizado_alquiler' => Carbon::now(),
                ]);

                DB::table('tbl_contrato')->insertOrIgnore([
                    'id_alquiler_fk' => $alquilerId,
                    'url_pdf_contrato' => null,
                    'estado_contrato' => 'pendiente',
                    'creado_contrato' => Carbon::now(),
                    'actualizado_contrato' => Carbon::now(),
                ]);

                DB::table('tbl_propiedad')
                    ->where('id_propiedad', $solicitud->id_propiedad_fk)
                    ->update([
                        'estado_propiedad' => 'alquilada',
                        'actualizado_propiedad' => Carbon::now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $estado === 'activo' ? 'Solicitud aprobada y alquiler formalizado.' : 'Solicitud rechazada.',
                'estado' => $estado,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cambiar estado de solicitud', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el cambio de estado: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function obtenerIdArrendador(Request $request): int
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $usuarioAutenticado = \Illuminate\Support\Facades\Auth::user();
            if (
                $usuarioAutenticado && DB::table('tbl_rol_usuario as ru')
                ->join('tbl_rol as r', 'r.id_rol', '=', 'ru.id_rol_fk')
                ->where('ru.id_usuario_fk', $usuarioAutenticado->id_usuario)
                ->where('r.slug_rol', 'arrendador')
                ->exists()
            ) {
                return (int) $usuarioAutenticado->id_usuario;
            }
        }

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
