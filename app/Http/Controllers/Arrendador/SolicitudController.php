<?php

namespace App\Http\Controllers\Arrendador;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'p.direccion_propiedad',
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
}
