<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $solicitud = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->where('tbl_solicitud_arrendador.id_solicitud_arrendador', $id)
            ->select(
              'tbl_solicitud_arrendador.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario',
              'tbl_usuario.telefono_usuario',
              'tbl_usuario.creado_usuario'
            )
            ->first();

        return response()->json($solicitud);
    }

    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_solicitud_arrendador')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_solicitud_arrendador.id_usuario_fk')
            ->select(
              'tbl_solicitud_arrendador.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario'
            );

        if ($request->estado) {
            $query->where('estado_solicitud_arrendador',
              $request->estado);
        }

        if ($request->ciudad) {
            $query->where('datos_solicitud_arrendador','like',
              '%"ciudad":"' . $request->ciudad . '"%');
        }

        if ($request->q) {
            $query->where('tbl_usuario.nombre_usuario','like',
              '%' . $request->q . '%');
        }

        $solicitudes = $query
            ->orderBy('tbl_solicitud_arrendador.creado_solicitud_arrendador','desc')
            ->get();

        return response()->json([
            'solicitudes' => $solicitudes,
            'total' => $solicitudes->count()
        ]);
    }
}
