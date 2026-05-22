<?php

namespace App\Http\Controllers\Admin;

use App\Models\SolicitudArrendador;
use App\Models\SolicitudGestor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        $solicitudesNuevas = $this->contarSolicitudesNuevas();

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

        $ultimasSolicitudes = $this->obtenerSolicitudesNuevas()->values();

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

        $ultimasIncidenciasInactivas = $this->obtenerIncidenciasInactivas()->values();

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

    public function filtrarIncidenciasInactivas(Request $request)
    {
      $estado = $request->input('estado', 'all');
      $busqueda = mb_strtolower(trim((string) $request->input('q', '')));

      $incidencias = $this->obtenerIncidenciasInactivas();

      if (in_array($estado, ['abierta', 'esperando_decision', 'esperando_pago', 'solucionada'], true)) {
        $incidencias = $incidencias->where('estado_incidencia', $estado);
      }

      if ($busqueda !== '') {
        $incidencias = $incidencias->filter(function ($incidencia) use ($busqueda) {
          $texto = mb_strtolower(implode(' ', [
            $incidencia->titulo_incidencia ?? '',
            $incidencia->titulo_propiedad ?? '',
            $incidencia->ciudad_propiedad ?? '',
            $incidencia->nombre_categoria ?? '',
            $incidencia->nombre_inquilino ?? '',
            $incidencia->nombre_arrendador ?? '',
            $incidencia->nombre_gestor ?? '',
          ]));

          return str_contains($texto, $busqueda);
        });
      }

      $datos = $incidencias->values()->map(function ($incidencia) {
        return [
          'id_incidencia' => $incidencia->id_incidencia,
          'titulo_incidencia' => $incidencia->titulo_incidencia,
          'prioridad_incidencia' => $incidencia->prioridad_incidencia,
          'estado_incidencia' => $incidencia->estado_incidencia,
          'actualizado_incidencia' => $incidencia->actualizado_incidencia,
          'titulo_propiedad' => $incidencia->titulo_propiedad,
          'direccion_propiedad' => $incidencia->direccion_propiedad,
          'ciudad_propiedad' => $incidencia->ciudad_propiedad,
          'nombre_categoria' => $incidencia->nombre_categoria,
          'nombre_inquilino' => $incidencia->nombre_inquilino,
          'nombre_arrendador' => $incidencia->nombre_arrendador,
          'nombre_gestor' => $incidencia->nombre_gestor,
          'encargado_pago' => $incidencia->encargado_pago,
        ];
      });

      return response()->json([
        'success' => true,
        'data' => $datos,
        'total' => $incidencias->count(),
      ]);
    }

    public function filtrarSolicitudesNuevas(Request $request)
    {
      $tipo = $request->input('tipo', 'all');
      $busqueda = mb_strtolower(trim((string) $request->input('q', '')));

      $solicitudes = $this->obtenerSolicitudesNuevas();

      if (in_array($tipo, ['arrendador', 'gestor'], true)) {
        $solicitudes = $solicitudes->where('tipo_solicitud', $tipo);
      }

      if ($busqueda !== '') {
        $solicitudes = $solicitudes->filter(function ($solicitud) use ($busqueda) {
          $texto = mb_strtolower(implode(' ', [
            $solicitud->nombre_usuario ?? '',
            $solicitud->email_usuario ?? '',
            $solicitud->descripcion_solicitud ?? '',
            $solicitud->experiencia_solicitud ?? '',
            $solicitud->direccion_fiscal_solicitud ?? '',
          ]));

          return str_contains($texto, $busqueda);
        });
      }

      $datos = $solicitudes
        ->values()
        ->map(function ($solicitud) {
          return [
            'id_solicitud' => $solicitud->id_solicitud,
            'tipo_solicitud' => $solicitud->tipo_solicitud,
            'tipo_label' => $solicitud->tipo_label,
            'nombre_usuario' => $solicitud->nombre_usuario,
            'email_usuario' => $solicitud->email_usuario,
            'telefono_usuario' => $solicitud->telefono_usuario,
            'descripcion_solicitud' => $solicitud->descripcion_solicitud,
            'experiencia_solicitud' => $solicitud->experiencia_solicitud ?? null,
            'direccion_fiscal_solicitud' => $solicitud->direccion_fiscal_solicitud ?? null,
            'creado_solicitud' => $solicitud->creado_solicitud,
          ];
        });

      return response()->json([
        'success' => true,
        'data' => $datos,
        'total' => $solicitudes->count(),
      ]);
    }

      private function obtenerIncidenciasInactivas(): Collection
      {
        $incidencias = DB::table('tbl_incidencia')
          ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_incidencia.id_propiedad_fk')
          ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'tbl_incidencia.id_reporta_fk')
          ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
          ->leftJoin('tbl_usuario as gestor', 'gestor.id_usuario', '=', 'tbl_incidencia.id_asignado_fk')
          ->leftJoin('tbl_categoria', 'tbl_categoria.id_categoria', '=', 'tbl_incidencia.id_categoria_fk')
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
          ->where('tbl_incidencia.actualizado_incidencia', '<', Carbon::now()->subWeeks(2))
          ->orderBy('tbl_incidencia.actualizado_incidencia', 'asc')
          ->limit(10)
          ->get();

        return collect($incidencias)->map(function ($inc) {
          $encargadoPago = null;

          if ($inc->estado_incidencia === 'esperando_pago') {
            $encargadoPago = $inc->nombre_gestor ? $inc->nombre_gestor : $inc->nombre_arrendador;
          }

          $inc->inactivo = true;
          $inc->encargado_pago = $encargadoPago;

          return $inc;
        });
      }

      private function contarSolicitudesNuevas(): int
      {
        return $this->obtenerSolicitudesNuevas()->count();
      }

      private function obtenerSolicitudesNuevas(): Collection
      {
        $solicitudesArrendador = DB::table('tbl_solicitud_arrendador as s')
          ->join('tbl_usuario as u', 'u.id_usuario', '=', 's.id_usuario_fk')
          ->where('s.estado_solicitud_arrendador', 'pendiente')
          ->select(
            's.id_solicitud_arrendador as id_solicitud',
            DB::raw("'arrendador' as tipo_solicitud"),
            DB::raw("'Arrendador' as tipo_label"),
            'u.nombre_usuario',
            'u.email_usuario',
            'u.telefono_usuario',
            's.descripcion_solicitud',
            's.direccion_fiscal_solicitud',
            's.creado_solicitud_arrendador as creado_solicitud',
            's.actualizado_solicitud_arrendador as actualizado_solicitud'
          )
          ->get();

        $solicitudesGestor = DB::table('tbl_solicitud_gestor as s')
          ->join('tbl_usuario as u', 'u.id_usuario', '=', 's.id_usuario_fk')
          ->where('s.estado_solicitud_gestor', 'pendiente')
          ->select(
            's.id_solicitud_gestor as id_solicitud',
            DB::raw("'gestor' as tipo_solicitud"),
            DB::raw("'Gestor' as tipo_label"),
            'u.nombre_usuario',
            'u.email_usuario',
            'u.telefono_usuario',
            's.descripcion_solicitud',
            's.experiencia_solicitud',
            DB::raw('NULL as direccion_fiscal_solicitud'),
            's.creado_solicitud_gestor as creado_solicitud',
            's.actualizado_solicitud_gestor as actualizado_solicitud'
          )
          ->get();

        return $solicitudesArrendador
          ->merge($solicitudesGestor)
          ->sortByDesc('creado_solicitud')
          ->values();
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
