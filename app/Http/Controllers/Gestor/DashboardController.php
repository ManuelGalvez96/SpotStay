<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $gestorId = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario', 'tbl_rol_usuario.id_usuario_fk', '=', 'tbl_usuario.id_usuario')
            ->join('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol', 'gestor')
            ->orderBy('tbl_usuario.id_usuario')
            ->value('tbl_usuario.id_usuario');

        $incidenciasNuevas = DB::table('tbl_incidencia')
            ->where('estado_incidencia', 'abierta')
            ->count();

        $incidenciasEnProceso = DB::table('tbl_incidencia')
            ->where('estado_incidencia', 'en_proceso')
            ->count();

        $incidenciasEsperandoAccion = DB::table('tbl_incidencia')
            ->whereIn('estado_incidencia', ['abierta', 'en_proceso'])
            ->where(function ($query) {
                $query->whereIn('prioridad_incidencia', ['alta', 'urgente'])
                    ->orWhere('creado_incidencia', '<=', Carbon::now()->subDays(2));
            })
            ->count();

        $incidenciasRecientes = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->select(
                'tbl_incidencia.id_incidencia',
                'tbl_incidencia.titulo_incidencia',
                'tbl_incidencia.estado_incidencia',
                'tbl_incidencia.prioridad_incidencia',
                'tbl_incidencia.creado_incidencia',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.direccion_propiedad',
                'tbl_propiedad.ciudad_propiedad'
            )
            ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
            ->limit(8)
            ->get();

        $incidenciasUrgentes = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->select(
                'tbl_incidencia.id_incidencia',
                'tbl_incidencia.titulo_incidencia',
                'tbl_incidencia.prioridad_incidencia',
                'tbl_incidencia.creado_incidencia',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.direccion_propiedad'
            )
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
            ->where(function ($query) {
                $query->whereIn('tbl_incidencia.prioridad_incidencia', ['alta', 'urgente'])
                    ->orWhere('tbl_incidencia.creado_incidencia', '<=', Carbon::now()->subDays(5));
            })
            ->orderByRaw("CASE WHEN prioridad_incidencia = 'urgente' THEN 1 WHEN prioridad_incidencia = 'alta' THEN 2 ELSE 3 END")
            ->orderBy('tbl_incidencia.creado_incidencia', 'asc')
            ->limit(5)
            ->get();

        $subQueryIncidenciasActivas = DB::table('tbl_incidencia')
            ->select('id_propiedad_fk', DB::raw('COUNT(*) as incidencias_activas'))
            ->whereIn('estado_incidencia', ['abierta', 'en_proceso'])
            ->groupBy('id_propiedad_fk');

        $propiedadesAsignadas = DB::table('tbl_propiedad')
            ->leftJoinSub($subQueryIncidenciasActivas, 'inc_activas', function ($join) {
                $join->on('inc_activas.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->select(
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                'tbl_propiedad.direccion_propiedad',
                'tbl_propiedad.ciudad_propiedad',
                DB::raw('COALESCE(inc_activas.incidencias_activas, 0) as incidencias_activas')
            )
            ->when($gestorId, function ($query) use ($gestorId) {
                $query->where('tbl_propiedad.id_gestor_fk', $gestorId);
            })
            ->orderByDesc('incidencias_activas')
            ->orderBy('tbl_propiedad.titulo_propiedad')
            ->limit(6)
            ->get();

        $esperandoArrendador = DB::table('tbl_historial_incidencia')
            ->join('tbl_incidencia', 'tbl_incidencia.id_incidencia', '=', 'tbl_historial_incidencia.id_incidencia_fk')
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
            ->where('tbl_historial_incidencia.comentario_historial', 'like', '%arrendador%')
            ->count();

        $esperandoEmpresa = DB::table('tbl_historial_incidencia')
            ->join('tbl_incidencia', 'tbl_incidencia.id_incidencia', '=', 'tbl_historial_incidencia.id_incidencia_fk')
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
            ->where('tbl_historial_incidencia.comentario_historial', 'like', '%empresa%')
            ->count();

        $esperandoInquilino = DB::table('tbl_historial_incidencia')
            ->join('tbl_incidencia', 'tbl_incidencia.id_incidencia', '=', 'tbl_historial_incidencia.id_incidencia_fk')
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'en_proceso'])
            ->where('tbl_historial_incidencia.comentario_historial', 'like', '%inquilino%')
            ->count();

        $totalEsperandoDetalle = max(1, $esperandoArrendador + $esperandoEmpresa + $esperandoInquilino);

        $notificaciones = DB::table('tbl_notificacion')
            ->when($gestorId, function ($query) use ($gestorId) {
                $query->where('id_usuario_fk', $gestorId);
            })
            ->whereIn('tipo_notificacion', ['nueva_incidencia', 'mensaje_nuevo', 'incidencia_actualizada', 'alquiler_pendiente'])
            ->orderBy('creado_notificacion', 'desc')
            ->limit(6)
            ->get();

        $resumenEstados = [
            'abierta' => DB::table('tbl_incidencia')->where('estado_incidencia', 'abierta')->count(),
            'en_proceso' => DB::table('tbl_incidencia')->where('estado_incidencia', 'en_proceso')->count(),
            'resuelta' => DB::table('tbl_incidencia')->where('estado_incidencia', 'resuelta')->count(),
        ];

        return view('gestor.dashboard', compact(
            'incidenciasNuevas',
            'incidenciasEnProceso',
            'incidenciasEsperandoAccion',
            'incidenciasRecientes',
            'incidenciasUrgentes',
            'propiedadesAsignadas',
            'esperandoArrendador',
            'esperandoEmpresa',
            'esperandoInquilino',
            'totalEsperandoDetalle',
            'notificaciones',
            'resumenEstados'
        ));
    }
}
