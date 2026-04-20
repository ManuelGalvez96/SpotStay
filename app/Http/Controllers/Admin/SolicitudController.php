<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SolicitudArrendador;

class SolicitudController extends Controller
{
    public function index()
    {
        $solicitudesPendientes = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->where('estado_solicitud_arrendador','pendiente')
            ->select(
              'tbl_solicitud_arrendador.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario',
              'tbl_usuario.telefono_usuario'
            )
            ->orderBy('tbl_solicitud_arrendador.creado_solicitud_arrendador','desc')
            ->paginate(10);

        $aprobadas = DB::table('tbl_solicitud_arrendador')
            ->where('estado_solicitud_arrendador','aprobada')
            ->whereMonth('actualizado_solicitud_arrendador',
              Carbon::now()->month)
            ->whereYear('actualizado_solicitud_arrendador',
              Carbon::now()->year)
            ->count();

        $rechazadas = DB::table('tbl_solicitud_arrendador')
            ->where('estado_solicitud_arrendador','rechazada')
            ->whereMonth('actualizado_solicitud_arrendador',
              Carbon::now()->month)
            ->whereYear('actualizado_solicitud_arrendador',
              Carbon::now()->year)
            ->count();

        $totalSolicitudes = DB::table('tbl_solicitud_arrendador')
            ->count();

        $tiempoMedio = 4.2;

        $ultimasAprobadas = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->where('estado_solicitud_arrendador','aprobada')
            ->select(
              'tbl_solicitud_arrendador.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario'
            )
            ->orderBy('tbl_solicitud_arrendador.actualizado_solicitud_arrendador','desc')
            ->limit(5)
            ->get();

        $ultimasRechazadas = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->where('estado_solicitud_arrendador','rechazada')
            ->select(
              'tbl_solicitud_arrendador.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario'
            )
            ->orderBy('tbl_solicitud_arrendador.actualizado_solicitud_arrendador','desc')
            ->limit(3)
            ->get();

        return view('admin.solicitudes', compact(
            'solicitudesPendientes',
            'aprobadas',
            'rechazadas',
            'totalSolicitudes',
            'tiempoMedio',
            'ultimasAprobadas',
            'ultimasRechazadas'
        ));
    }

    public function aprobar($id)
    {
        DB::beginTransaction();
        try {
            $solicitud = DB::table('tbl_solicitud_arrendador')
                ->where('id_solicitud_arrendador', $id)
                ->first();

            $idAdmin = DB::table('tbl_usuario')
                ->where('email_usuario','admin@spotstay.com')
                ->value('id_usuario');

            $idRolArrendador = DB::table('tbl_rol')
                ->where('slug_rol','arrendador')
                ->value('id_rol');

            DB::table('tbl_solicitud_arrendador')
                ->where('id_solicitud_arrendador', $id)
                ->update([
                    'estado_solicitud_arrendador' => 'aprobada',
                    'id_admin_revisa_fk' => $idAdmin,
                    'actualizado_solicitud_arrendador' => Carbon::now()
                ]);

            $tieneRol = DB::table('tbl_rol_usuario')
                ->where('id_usuario_fk', $solicitud->id_usuario_fk)
                ->where('id_rol_fk', $idRolArrendador)
                ->exists();

            if (!$tieneRol) {
                DB::table('tbl_rol_usuario')->insert([
                    'id_usuario_fk' => $solicitud->id_usuario_fk,
                    'id_rol_fk' => $idRolArrendador,
                    'asignado_rol_usuario' => Carbon::now()
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rechazar(Request $request, $id)
    {
        $idAdmin = DB::table('tbl_usuario')
            ->where('email_usuario','admin@spotstay.com')
            ->value('id_usuario');

        DB::table('tbl_solicitud_arrendador')
            ->where('id_solicitud_arrendador', $id)
            ->update([
                'estado_solicitud_arrendador' => 'rechazada',
                'id_admin_revisa_fk' => $idAdmin,
                'notas_solicitud_arrendador' => $request->notas ?? null,
                'actualizado_solicitud_arrendador' => Carbon::now()
            ]);

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $solicitud = SolicitudArrendador::with('usuario:id_usuario,nombre_usuario,email_usuario,telefono_usuario,creado_usuario')
            ->where('id_solicitud_arrendador', $id)
            ->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        return response()->json([
            'id_solicitud_arrendador' => $solicitud->id_solicitud_arrendador,
            'id_usuario_fk' => $solicitud->id_usuario_fk,
            'datos_solicitud_arrendador' => $solicitud->datos_solicitud_arrendador,
            'estado_solicitud_arrendador' => $solicitud->estado_solicitud_arrendador,
            'notas_solicitud_arrendador' => $solicitud->notas_solicitud_arrendador,
            'creado_solicitud_arrendador' => $solicitud->creado_solicitud_arrendador,
            'actualizado_solicitud_arrendador' => $solicitud->actualizado_solicitud_arrendador,
            'nombre_usuario' => $solicitud->usuario?->nombre_usuario ?? '—',
            'email_usuario' => $solicitud->usuario?->email_usuario ?? '—',
            'telefono_usuario' => $solicitud->usuario?->telefono_usuario ?? '—',
        ]);
    }

    public function filtrar(Request $request)
    {
        $query = SolicitudArrendador::with('usuario:id_usuario,nombre_usuario,email_usuario')
            ->select('tbl_solicitud_arrendador.*');

        if ($request->estado) {
            $query->where('estado_solicitud_arrendador', $request->estado);
        }

        if ($request->ciudad) {
            $query->whereJsonContains('datos_solicitud_arrendador->ciudad', $request->ciudad);
        }

        if ($request->q) {
            $query->whereHas('usuario', function ($q) use ($request) {
                $q->where('nombre_usuario', 'like', '%' . $request->q . '%');
            });
        }

        $solicitudesPaginadas = $query
            ->orderBy('creado_solicitud_arrendador', 'desc')
            ->paginate(6);

        /* Transformar datos para incluir nombre_usuario y email_usuario */
        $items = $solicitudesPaginadas->items();
        $data = array_map(function($solicitud) {
            /* Asegurar que datos_solicitud_arrendador es un array, no una cadena JSON */
            $datos = $solicitud->datos_solicitud_arrendador;
            if (is_string($datos)) {
                $datos = json_decode($datos, true) ?? [];
            }
            
            return [
                'id_solicitud_arrendador' => $solicitud->id_solicitud_arrendador,
                'id_usuario_fk' => $solicitud->id_usuario_fk,
                'datos_solicitud_arrendador' => $datos,
                'estado_solicitud_arrendador' => $solicitud->estado_solicitud_arrendador,
                'creado_solicitud_arrendador' => $solicitud->creado_solicitud_arrendador,
                'actualizado_solicitud_arrendador' => $solicitud->actualizado_solicitud_arrendador,
                'nombre_usuario' => $solicitud->usuario?->nombre_usuario ?? '—',
                'email_usuario' => $solicitud->usuario?->email_usuario ?? '—',
            ];
        }, $items);

        return response()->json([
            'data' => $data,
            'total' => $solicitudesPaginadas->total(),
            'current_page' => $solicitudesPaginadas->currentPage(),
            'last_page' => $solicitudesPaginadas->lastPage(),
            'per_page' => $solicitudesPaginadas->perPage(),
            'from' => $solicitudesPaginadas->firstItem(),
            'to' => $solicitudesPaginadas->lastItem()
        ]);
    }
}
