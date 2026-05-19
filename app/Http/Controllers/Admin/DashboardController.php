<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsuarios = DB::table('tbl_usuario')->count();

        $propiedadesActivas = DB::table('tbl_propiedad')
            ->whereIn('estado_propiedad', ['publicada', 'alquilada'])
            ->count();

        $alquileresPendientes = DB::table('tbl_alquiler')
            ->where('estado_alquiler', 'pendiente')
            ->count();

        $solicitudesNuevas = DB::table('tbl_solicitud_arrendador')
            ->where('estado_solicitud_arrendador', 'pendiente')
            ->count();

        $ultimosAlquileres = DB::table('tbl_alquiler')
            ->join('tbl_propiedad',
              'tbl_propiedad.id_propiedad', '=',
              'tbl_alquiler.id_propiedad_fk')
            ->join('tbl_usuario as inquilino',
              'inquilino.id_usuario', '=',
              'tbl_alquiler.id_inquilino_fk')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->select(
              'tbl_alquiler.id_alquiler',
              'tbl_propiedad.titulo_propiedad',
              DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'tbl_propiedad.ciudad_propiedad',
              'inquilino.nombre_usuario as nombre_inquilino',
              'arrendador.nombre_usuario as nombre_arrendador',
              'tbl_alquiler.estado_alquiler',
              'tbl_alquiler.creado_alquiler'
            )
            ->where('tbl_alquiler.estado_alquiler', 'pendiente')
            ->orderBy('tbl_alquiler.creado_alquiler', 'desc')
            ->limit(5)
            ->get();

        $ultimasSolicitudes = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->where('estado_solicitud_arrendador', 'pendiente')
            ->select(
              'tbl_solicitud_arrendador.id_solicitud_arrendador',
              'tbl_usuario.nombre_usuario',
              'tbl_solicitud_arrendador.creado_solicitud_arrendador'
            )
            ->orderBy('tbl_solicitud_arrendador.creado_solicitud_arrendador', 'desc')
            ->limit(3)
            ->get();

        $usuariosPorRol = DB::table('tbl_rol')
            ->join('tbl_rol_usuario',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk')
            ->select(
              'tbl_rol.nombre_rol',
              DB::raw('COUNT(*) as total')
            )
            ->groupBy('tbl_rol.id_rol', 'tbl_rol.nombre_rol')
            ->get();

        $actividadReciente = DB::table('tbl_notificacion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_notificacion.id_usuario_fk')
            ->select(
              'tbl_notificacion.titulo_notificacion',
              'tbl_notificacion.tipo_notificacion',
              'tbl_notificacion.color_notificacion',
              'tbl_notificacion.creado_notificacion',
              'tbl_usuario.nombre_usuario'
            )
            ->orderBy('tbl_notificacion.creado_notificacion', 'desc')
            ->limit(5)
            ->get();

        // Incidencias inactivas (sin cambios hace 2+ semanas) en estados específicos
        $incidenciasInactivas = DB::table('tbl_incidencia')
            ->join('tbl_propiedad',
              'tbl_propiedad.id_propiedad', '=',
              'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario as inquilino',
              'inquilino.id_usuario', '=',
              'tbl_incidencia.id_reporta_fk')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin('tbl_usuario as gestor',
              'gestor.id_usuario', '=',
              'tbl_incidencia.id_asignado_fk')
            ->leftJoin('tbl_categoria',
              'tbl_categoria.id_categoria', '=',
              'tbl_incidencia.id_categoria_fk')
            ->select(
              'tbl_incidencia.id_incidencia',
              'tbl_incidencia.titulo_incidencia',
              'tbl_incidencia.prioridad_incidencia',
              'tbl_incidencia.estado_incidencia',
              'tbl_incidencia.actualizado_incidencia',
              'tbl_propiedad.titulo_propiedad',
              DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'tbl_propiedad.ciudad_propiedad',
              'tbl_categoria.nombre_categoria',
              'inquilino.nombre_usuario as nombre_inquilino',
              'inquilino.email_usuario as email_inquilino',
              'arrendador.nombre_usuario as nombre_arrendador',
              'arrendador.email_usuario as email_arrendador',
              'gestor.nombre_usuario as nombre_gestor',
              'gestor.email_usuario as email_gestor'
            )
            ->whereIn('tbl_incidencia.estado_incidencia', ['abierta', 'esperando_decision', 'esperando_pago', 'solucionada'])
            ->orderBy('tbl_incidencia.actualizado_incidencia', 'asc')
            ->limit(10)
            ->get();

        // Marcar como inactivas las que hace 2+ semanas que no cambian
        $ultimasIncidenciasInactivas = collect($incidenciasInactivas)->map(function($inc) {
            $diferencia = Carbon::parse($inc->actualizado_incidencia)->diffInWeeks(Carbon::now());
            $inc->inactivo = $diferencia >= 2;
            
            // Determinar encargado de pago
            $encargadoPago = null;
            if ($inc->estado_incidencia === 'esperando_pago') {
                // Lógica: si el gestor existe, es el gestor; si no, es el arrendador
                $encargadoPago = $inc->nombre_gestor ? $inc->nombre_gestor : $inc->nombre_arrendador;
            }
            $inc->encargado_pago = $encargadoPago;
            
            return $inc;
        })->filter(function($inc) {
            return $inc->inactivo === true;
        })->take(5);

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'propiedadesActivas',
            'alquileresPendientes',
            'solicitudesNuevas',
            'ultimosAlquileres',
            'ultimasSolicitudes',
            'usuariosPorRol',
            'actividadReciente',
            'ultimasIncidenciasInactivas'
        ));
    }

    public function aprobarAlquiler($id)
    {
        DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->update([
                'estado_alquiler' => 'activo',
                'aprobado_alquiler' => Carbon::now(),
                'actualizado_alquiler' => Carbon::now()
            ]);
        return response()->json(['success' => true]);
    }

    public function rechazarAlquiler($id)
    {
        DB::table('tbl_alquiler')
            ->where('id_alquiler', $id)
            ->update([
                'estado_alquiler' => 'rechazado',
                'actualizado_alquiler' => Carbon::now()
            ]);
        return response()->json(['success' => true]);
    }

    public function stats()
    {
        $usuariosPorRol = DB::table('tbl_rol')
            ->join('tbl_rol_usuario',
              'tbl_rol.id_rol', '=',
              'tbl_rol_usuario.id_rol_fk')
            ->select(
              'tbl_rol.nombre_rol',
              DB::raw('COUNT(*) as total')
            )
            ->groupBy('tbl_rol.id_rol', 'tbl_rol.nombre_rol')
            ->get();

        // Mapear roles a los esperados por el gráfico
        $stats = [
            'inquilinos' => 0,
            'arrendadores' => 0,
            'miembros' => 0,
            'gestores' => 0
        ];

        foreach ($usuariosPorRol as $rol) {
            $nombre = strtolower($rol->nombre_rol);
            if (strpos($nombre, 'inquilino') !== false) {
                $stats['inquilinos'] = $rol->total;
            } elseif (strpos($nombre, 'arrendador') !== false) {
                $stats['arrendadores'] = $rol->total;
            } elseif (strpos($nombre, 'miembro') !== false) {
                $stats['miembros'] = $rol->total;
            } elseif (strpos($nombre, 'gestor') !== false) {
                $stats['gestores'] = $rol->total;
            }
        }

        return response()->json([
            'stats' => $stats,
            'data' => [
                $stats['inquilinos'],
                $stats['arrendadores'],
                $stats['miembros'],
                $stats['gestores']
            ]
        ]);
    }
}
