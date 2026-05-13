<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use GestorPermisosTrait;
    public function index()
    {
        $gestor = Auth::user();
        $gestorId = (int) ($gestor?->id_usuario ?? 0);

        $baseIncidencias = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->when($gestorId > 0, function ($query) use ($gestorId) {
                $query->where(function ($scope) use ($gestorId) {
                    $scope->where('tbl_incidencia.id_asignado_fk', $gestorId)
                        ->orWhere(function ($legacy) use ($gestorId) {
                            $legacy->whereNull('tbl_incidencia.id_asignado_fk')
                                ->where('tbl_propiedad.id_gestor_fk', $gestorId);
                        });
                });
            })
            ->whereExists(function ($query) use ($gestorId) {
                $query->selectRaw(1)
                    ->from('tbl_propiedad_permisos')
                    ->whereColumn('tbl_propiedad_permisos.id_propiedad_fk', 'tbl_propiedad.id_propiedad')
                    ->where('tbl_propiedad_permisos.id_gestor_fk', $gestorId)
                    ->where('tbl_propiedad_permisos.incidencias', true);
            });

        $incidenciasNuevas = (clone $baseIncidencias)
            ->where('tbl_incidencia.estado_incidencia', 'abierta')
            ->count();

        $incidenciasEnProceso = (clone $baseIncidencias)
            ->where('tbl_incidencia.estado_incidencia', 'en_proceso')
            ->count();

        $incidenciasEsperandoAccion = (clone $baseIncidencias)
            ->where('tbl_incidencia.estado_incidencia', 'esperando')
            ->count();

        $incidenciasRecientes = (clone $baseIncidencias)
            ->select(
                'tbl_incidencia.id_incidencia',
                'tbl_incidencia.titulo_incidencia',
                'tbl_incidencia.estado_incidencia',
                'tbl_incidencia.prioridad_incidencia',
                'tbl_incidencia.creado_incidencia',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'tbl_propiedad.ciudad_propiedad'
            )
            ->orderBy('tbl_incidencia.creado_incidencia', 'desc')
            ->limit(5)
            ->get();

        $incidenciasUrgentes = (clone $baseIncidencias)
            ->select(
                'tbl_incidencia.id_incidencia',
                'tbl_incidencia.titulo_incidencia',
                'tbl_incidencia.prioridad_incidencia',
                'tbl_incidencia.creado_incidencia',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad")
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
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->select('id_propiedad_fk', DB::raw('COUNT(*) as incidencias_activas'))
            ->whereIn('estado_incidencia', ['abierta', 'en_proceso'])
            ->when($gestorId > 0, function ($query) use ($gestorId) {
                $query->where('tbl_propiedad.id_gestor_fk', $gestorId);
            })
            ->groupBy('id_propiedad_fk');

        $propiedadesAsignadas = DB::table('tbl_propiedad')
            ->leftJoinSub($subQueryIncidenciasActivas, 'inc_activas', function ($join) {
                $join->on('inc_activas.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->select(
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
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

        $esperasDetalle = DB::table('tbl_incidencia')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
            ->selectRaw("SUM(CASE WHEN esperando_de_incidencia = 'arrendador' THEN 1 ELSE 0 END) as esperando_arrendador")
            ->selectRaw("SUM(CASE WHEN esperando_de_incidencia = 'empresa' THEN 1 ELSE 0 END) as esperando_empresa")
            ->selectRaw("SUM(CASE WHEN esperando_de_incidencia = 'inquilino' THEN 1 ELSE 0 END) as esperando_inquilino")
            ->where('estado_incidencia', 'esperando')
            ->when($gestorId > 0, function ($query) use ($gestorId) {
                $query->where('tbl_propiedad.id_gestor_fk', $gestorId);
            })
            ->first();

        $esperandoArrendador = (int) ($esperasDetalle->esperando_arrendador ?? 0);
        $esperandoEmpresa = (int) ($esperasDetalle->esperando_empresa ?? 0);
        $esperandoInquilino = (int) ($esperasDetalle->esperando_inquilino ?? 0);

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
            'abierta' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'abierta')->count(),
            'en_proceso' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'en_proceso')->count(),
            'esperando' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'esperando')->count(),
        ];

        $permisosDashboard = [];
        foreach ($propiedadesAsignadas as $p) {
            $permisosDashboard[$p->id_propiedad] = $this->getPermisosPropiedad($gestorId, (int) $p->id_propiedad);
        }

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
            'resumenEstados',
            'permisosDashboard'
        ));
    }
}
