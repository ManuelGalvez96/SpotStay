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
                'tbl_incidencia.estado_incidencia',
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

        $subAlquilerActivo = DB::table('tbl_alquiler')
            ->select('id_propiedad_fk', 'id_alquiler', 'id_inquilino_fk', 'fecha_inicio_alquiler', 'fecha_fin_alquiler')
            ->where('estado_alquiler', 'activo');

        $subPagosPendientes = DB::table('tbl_alquiler')
            ->join('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->select('tbl_alquiler.id_propiedad_fk', DB::raw('COUNT(*) as total_pagos_pendientes'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pendiente')
            ->groupBy('tbl_alquiler.id_propiedad_fk');

        $subPagosAtrasados = DB::table('tbl_alquiler')
            ->join('tbl_alquiler_cuota', 'tbl_alquiler_cuota.id_alquiler_fk', '=', 'tbl_alquiler.id_alquiler')
            ->select('tbl_alquiler.id_propiedad_fk', DB::raw('COUNT(*) as total_pagos_atrasados'))
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'atrasado')
            ->groupBy('tbl_alquiler.id_propiedad_fk');

        $propiedadesAsignadas = DB::table('tbl_propiedad')
            ->leftJoinSub($subQueryIncidenciasActivas, 'inc_activas', function ($join) {
                $join->on('inc_activas.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subAlquilerActivo, 'alq_activo', function ($join) {
                $join->on('alq_activo.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subPagosPendientes, 'pagos_pendientes', function ($join) {
                $join->on('pagos_pendientes.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoinSub($subPagosAtrasados, 'pagos_atrasados', function ($join) {
                $join->on('pagos_atrasados.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad');
            })
            ->leftJoin('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'alq_activo.id_inquilino_fk')
            ->select(
                'tbl_propiedad.id_propiedad',
                'tbl_propiedad.titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'tbl_propiedad.ciudad_propiedad',
                DB::raw('COALESCE(inc_activas.incidencias_activas, 0) as incidencias_activas'),
                DB::raw('COALESCE(pagos_pendientes.total_pagos_pendientes, 0) as pagos_pendientes'),
                DB::raw('COALESCE(pagos_atrasados.total_pagos_atrasados, 0) as pagos_atrasados'),
                'alq_activo.id_alquiler',
                'alq_activo.fecha_inicio_alquiler',
                'alq_activo.fecha_fin_alquiler',
                'inquilino.nombre_usuario as nombre_inquilino'
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
            ->whereIn('tipo_notificacion', [
                'nueva_incidencia',
                'incidencia_actualizada',
                'pago_realizado',
                'pago_atrasado',
                'mensaje_nuevo',
                'presupuesto_creado',
                'gasto_creado',
                'alquiler_pendiente',
                'propiedad_estado',
                'alquiler_creado',
                'alquiler_aprobado',
                'contrato_firmado',
            ])
            ->select(
                'id_notificacion',
                'tipo_notificacion',
                'titulo_notificacion',
                'mensaje_notificacion',
                'url_notificacion',
                'icono_notificacion',
                'color_notificacion',
                'tipo_entidad_notificacion',
                'id_entidad_notificacion',
                'creado_notificacion'
            )
            ->orderBy('creado_notificacion', 'desc')
            ->limit(10)
            ->get();

        $mensajesSinLeer = DB::table('tbl_conversacion')
            ->join('tbl_conversacion_usuario', function ($join) use ($gestorId) {
                $join->on('tbl_conversacion_usuario.id_conversacion_fk', '=', 'tbl_conversacion.id_conversacion')
                    ->where('tbl_conversacion_usuario.id_usuario_fk', $gestorId);
            })
            ->leftJoin(DB::raw('(SELECT id_conversacion_fk, MAX(creado_mensaje) as ultimo_creado FROM tbl_mensaje GROUP BY id_conversacion_fk) as ult'), function ($join) {
                $join->on('ult.id_conversacion_fk', '=', 'tbl_conversacion.id_conversacion');
            })
            ->where(function ($query) {
                $query->whereNull('tbl_conversacion_usuario.ultima_lectura_conv_usuario')
                    ->orWhereColumn('ult.ultimo_creado', '>', 'tbl_conversacion_usuario.ultima_lectura_conv_usuario');
            })
            ->count();

        $pagosPendientesTotal = DB::table('tbl_alquiler_cuota')
            ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_propiedad.id_gestor_fk', $gestorId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pendiente')
            ->count();

        $contratosPorVencer = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_propiedad.id_gestor_fk', $gestorId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->whereBetween('tbl_alquiler.fecha_fin_alquiler', [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays(30)->endOfDay()
            ])
            ->count();

        $resumenEstados = [
            'abierta' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'abierta')->count(),
            'en_proceso' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'en_proceso')->count(),
            'esperando' => (clone $baseIncidencias)->where('tbl_incidencia.estado_incidencia', 'esperando')->count(),
        ];

        $totalesPropiedades = (object) [
            'total' => DB::table('tbl_propiedad')->where('id_gestor_fk', $gestorId)->count(),
            'con_alquiler' => DB::table('tbl_alquiler')
                ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
                ->where('tbl_propiedad.id_gestor_fk', $gestorId)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->count(),
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
            'mensajesSinLeer',
            'pagosPendientesTotal',
            'contratosPorVencer',
            'resumenEstados',
            'totalesPropiedades',
            'permisosDashboard'
        ));
    }
}
