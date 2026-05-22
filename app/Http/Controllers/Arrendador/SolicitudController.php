<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use App\Models\AlquilerCuota;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                's.id_solicitud_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                's.estado_solicitud_alquiler',
                's.fecha_inicio_solicitud_alquiler',
                's.creado_solicitud_alquiler'
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
            ->where('s.estado_solicitud_alquiler', 'activo')
            ->count();

        $rechazados = DB::table('tbl_solicitud_alquiler as s')
            ->join('tbl_propiedad as p', 'p.id_propiedad', '=', 's.id_propiedad_fk')
            ->where('p.id_arrendador_fk', $arrendadorId)
            ->where('s.estado_solicitud_alquiler', 'rechazado')
            ->count();

        return view('arrendador.solicitudes', [
            'arrendador' => $arrendador,
            'arrendadorId' => $arrendadorId,
            'avatarInicial' => $this->obtenerInicialAvatar($arrendador?->nombre_usuario),
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
                's.id_solicitud_alquiler',
                'p.titulo_propiedad',
                DB::raw($this->obtenerSelectDireccionPropiedad('p')),
                'inquilino.nombre_usuario as nombre_inquilino',
                'inquilino.email_usuario as email_inquilino',
                'inquilino.telefono_usuario as telefono_inquilino',
                's.estado_solicitud_alquiler',
                's.fecha_inicio_solicitud_alquiler',
                's.mensaje_solicitud_alquiler',
                'p.precio_propiedad as precio_alquiler',
                's.creado_solicitud_alquiler'
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

        if ($request->has('fecha_inicio_solicitud_alquiler')) {
            $datosActualizar['fecha_inicio_solicitud_alquiler'] = $request->input('fecha_inicio_solicitud_alquiler');
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
            Log::info('Solicitud no encontrada', ['id' => $id, 'arrendadorId' => $arrendadorId]);
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
                's.estado_solicitud_alquiler',
                'p.precio_propiedad'
            )
            ->first();

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la solicitud.',
            ], 404);
        }

        if ($estado !== 'activo') {
            DB::table('tbl_solicitud_alquiler')
                ->where('id_solicitud_alquiler', $id)
                ->update([
                    'estado_solicitud_alquiler' => $estado,
                    'actualizado_solicitud_alquiler' => Carbon::now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada.',
                'estado' => $estado,
            ]);
        }

        if ($solicitud->estado_solicitud_alquiler === 'activo') {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud ya está aprobada.',
            ], 409);
        }

        $existeAlquilerActivo = DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $solicitud->id_propiedad_fk)
            ->whereIn('estado_alquiler', ['pendiente', 'activo'])
            ->exists();

        if ($existeAlquilerActivo) {
            return response()->json([
                'success' => false,
                'message' => 'La propiedad ya tiene un alquiler activo o pendiente.',
            ], 409);
        }

        $idRolInquilino = DB::table('tbl_rol')
            ->where('slug_rol', 'inquilino')
            ->value('id_rol');

        if (!$idRolInquilino) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el rol de inquilino.',
            ], 500);
        }

        $debeCerrarSesion = false;

        DB::beginTransaction();

        try {
            $tieneRolInquilino = DB::table('tbl_rol_usuario')
                ->where('id_usuario_fk', $solicitud->id_usuario_fk)
                ->where('id_rol_fk', $idRolInquilino)
                ->exists();

            if (!$tieneRolInquilino) {
                DB::table('tbl_rol_usuario')->insert([
                    'id_usuario_fk' => $solicitud->id_usuario_fk,
                    'id_rol_fk' => $idRolInquilino,
                    'asignado_rol_usuario' => Carbon::now(),
                ]);
                $debeCerrarSesion = true;
            }

            $idAlquiler = DB::table('tbl_alquiler')->insertGetId([
                'id_propiedad_fk' => $solicitud->id_propiedad_fk,
                'id_inquilino_fk' => $solicitud->id_usuario_fk,
                'id_admin_aprueba_fk' => null,
                'fecha_inicio_alquiler' => $solicitud->fecha_inicio_solicitud_alquiler,
                'fecha_fin_alquiler' => null,
                'precio_alquiler' => $solicitud->precio_propiedad,
                'estado_alquiler' => 'activo',
                'aprobado_alquiler' => Carbon::now(),
                'creado_alquiler' => Carbon::now(),
                'actualizado_alquiler' => Carbon::now(),
            ]);

            $this->generarCuotasAlAprobar((object) [
                'id_alquiler' => $idAlquiler,
                'id_propiedad_fk' => $solicitud->id_propiedad_fk,
                'fecha_inicio_alquiler' => $solicitud->fecha_inicio_solicitud_alquiler,
                'fecha_fin_alquiler' => null,
            ]);

            DB::table('tbl_solicitud_alquiler')
                ->where('id_solicitud_alquiler', $id)
                ->update([
                    'estado_solicitud_alquiler' => $estado,
                    'actualizado_solicitud_alquiler' => Carbon::now(),
                ]);

            DB::table('tbl_propiedad')
                ->where('id_propiedad', $solicitud->id_propiedad_fk)
                ->update([
                    'estado_propiedad' => 'alquilada',
                    'actualizado_propiedad' => Carbon::now(),
                ]);

            if ($debeCerrarSesion) {
                DB::table('sessions')
                    ->where('user_id', $solicitud->id_usuario_fk)
                    ->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aprobar solicitud', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'No se pudo aprobar la solicitud: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada.',
            'estado' => $estado,
        ]);
    }

    private function generarCuotasAlAprobar(object $alquiler): void
    {
        if (!Schema::hasTable('tbl_alquiler_cuota')) {
            return;
        }

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $alquiler->id_propiedad_fk)
            ->select('precio_propiedad')
            ->first();

        $importeBase = round((float) ($propiedad->precio_propiedad ?? 0), 2);
        if ($importeBase <= 0) {
            return;
        }

        $inicio = Carbon::parse((string) $alquiler->fecha_inicio_alquiler)->startOfMonth();
        $limite = $alquiler->fecha_fin_alquiler
            ? Carbon::parse((string) $alquiler->fecha_fin_alquiler)->startOfMonth()
            : $inicio->copy()->addMonths(11);

        if ($limite->lessThan($inicio)) {
            return;
        }

        $diaVencimiento = Carbon::parse((string) $alquiler->fecha_inicio_alquiler)->day;

        $mesActualCuota = $inicio->copy();
        while ($mesActualCuota->lessThanOrEqualTo($limite)) {
            $ultimoDiaMes = (int) $mesActualCuota->copy()->endOfMonth()->day;
            $dia = min($diaVencimiento, $ultimoDiaMes);
            $fechaVencimiento = $mesActualCuota->copy()->day($dia)->toDateString();

            AlquilerCuota::firstOrCreate(
                [
                    'id_alquiler_fk' => (int) $alquiler->id_alquiler,
                    'mes_cuota' => $mesActualCuota->copy()->toDateString(),
                ],
                [
                    'importe_base' => $importeBase,
                    'estado' => 'pendiente',
                    'fecha_vencimiento' => $fechaVencimiento,
                    'pagado_en' => null,
                ]
            );

            $mesActualCuota->addMonth();
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

    private function obtenerInicialAvatar(?string $nombre): string
    {
        if (empty($nombre)) {
            return 'A';
        }

        return mb_strtoupper(mb_substr(trim($nombre), 0, 1));
    }
}
