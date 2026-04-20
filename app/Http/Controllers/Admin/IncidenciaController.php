<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IncidenciaController extends Controller
{
    public function index()
    {
        $queryBase = DB::table('tbl_incidencia')
            ->join('tbl_propiedad',
              'tbl_propiedad.id_propiedad','=',
              'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario as reporta',
              'reporta.id_usuario','=',
              'tbl_incidencia.id_reporta_fk')
            ->leftJoin('tbl_usuario as asignado',
              'asignado.id_usuario','=',
              'tbl_incidencia.id_asignado_fk')
            ->select(
              'tbl_incidencia.*',
              'tbl_propiedad.titulo_propiedad',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'tbl_propiedad.ciudad_propiedad',
              'reporta.nombre_usuario as nombre_inquilino',
              'asignado.nombre_usuario as nombre_gestor'
            );

        $abiertas = (clone $queryBase)
            ->where('tbl_incidencia.estado_incidencia','abierta')
            ->orderBy('tbl_incidencia.creado_incidencia','desc')
            ->get();

        $enProceso = (clone $queryBase)
            ->where('tbl_incidencia.estado_incidencia','en_proceso')
            ->orderBy('tbl_incidencia.creado_incidencia','desc')
            ->get();

        $resueltas = (clone $queryBase)
            ->where('tbl_incidencia.estado_incidencia','resuelta')
            ->orderBy('tbl_incidencia.creado_incidencia','desc')
            ->get();

        $cerradas = (clone $queryBase)
            ->where('tbl_incidencia.estado_incidencia','cerrada')
            ->orderBy('tbl_incidencia.creado_incidencia','desc')
            ->get();

        $totalAbiertas = $abiertas->count();
        $totalEnProceso = $enProceso->count();
        $totalResueltas = $resueltas->count();
        $totalCerradas = $cerradas->count();

        $urgentes = DB::table('tbl_incidencia')
            ->where('prioridad_incidencia','urgente')
            ->whereIn('estado_incidencia',['abierta','en_proceso'])
            ->count();

        $gestores = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol',
              'tbl_rol.id_rol','=',
              'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol','gestor')
            ->select('tbl_usuario.id_usuario','tbl_usuario.nombre_usuario')
            ->get();

        $propiedades = DB::table('tbl_propiedad')
            ->select('id_propiedad','titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'ciudad_propiedad')
            ->orderBy('titulo_propiedad','asc')
            ->get();

        $inquilinos = DB::table('tbl_usuario')
            ->join('tbl_rol_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_rol_usuario.id_usuario_fk')
            ->join('tbl_rol',
              'tbl_rol.id_rol','=',
              'tbl_rol_usuario.id_rol_fk')
            ->where('tbl_rol.slug_rol','inquilino')
            ->select('tbl_usuario.id_usuario','tbl_usuario.nombre_usuario','tbl_usuario.email_usuario')
            ->orderBy('tbl_usuario.nombre_usuario','asc')
            ->get();

        return view('admin.incidencias', compact(
            'abiertas',
            'enProceso',
            'resueltas',
            'cerradas',
            'totalAbiertas',
            'totalEnProceso',
            'totalResueltas',
            'totalCerradas',
            'urgentes',
            'gestores',
            'propiedades',
            'inquilinos'
        ));
    }

    public function show($id)
    {
        $incidencia = DB::table('tbl_incidencia')
            ->join('tbl_propiedad',
              'tbl_propiedad.id_propiedad','=',
              'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario as reporta',
              'reporta.id_usuario','=',
              'tbl_incidencia.id_reporta_fk')
            ->leftJoin('tbl_usuario as asignado',
              'asignado.id_usuario','=',
              'tbl_incidencia.id_asignado_fk')
            ->where('tbl_incidencia.id_incidencia', $id)
            ->select(
              'tbl_incidencia.*',
              'tbl_propiedad.titulo_propiedad',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'tbl_propiedad.ciudad_propiedad',
              'reporta.nombre_usuario as nombre_inquilino',
              'reporta.email_usuario as email_inquilino',
              'asignado.nombre_usuario as nombre_gestor'
            )
            ->first();

        $historial = DB::table('tbl_historial_incidencia')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_historial_incidencia.id_usuario_fk')
            ->where('id_incidencia_fk', $id)
            ->select(
              'tbl_historial_incidencia.*',
              'tbl_usuario.nombre_usuario'
            )
            ->orderBy('tbl_historial_incidencia.creado_historial','asc')
            ->get();

        return response()->json([
            'incidencia' => $incidencia,
            'historial' => $historial
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $idAdmin = DB::table('tbl_usuario')
                ->where('email_usuario','admin@spotstay.com')
                ->value('id_usuario');

            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'estado_incidencia' => $request->estado,
                    'actualizado_incidencia' => Carbon::now()
                ]);

            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $id,
                'id_usuario_fk' => $idAdmin,
                'comentario_historial' => $request->comentario ??
                    'Estado cambiado a ' . $request->estado,
                'cambio_estado_historial' => $request->estado,
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

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

    public function asignar(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $idAdmin = DB::table('tbl_usuario')
                ->where('email_usuario','admin@spotstay.com')
                ->value('id_usuario');

            $nombreGestor = DB::table('tbl_usuario')
                ->where('id_usuario', $request->id_gestor)
                ->value('nombre_usuario');

            DB::table('tbl_incidencia')
                ->where('id_incidencia', $id)
                ->update([
                    'id_asignado_fk' => $request->id_gestor,
                    'estado_incidencia' => 'en_proceso',
                    'actualizado_incidencia' => Carbon::now()
                ]);

            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $id,
                'id_usuario_fk' => $idAdmin,
                'comentario_historial' => 'Incidencia asignada a ' .
                    $nombreGestor,
                'cambio_estado_historial' => 'en_proceso',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

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

    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_incidencia')
            ->join('tbl_propiedad',
              'tbl_propiedad.id_propiedad','=',
              'tbl_incidencia.id_propiedad_fk')
            ->join('tbl_usuario as reporta',
              'reporta.id_usuario','=',
              'tbl_incidencia.id_reporta_fk')
            ->select(
              'tbl_incidencia.*',
              'tbl_propiedad.titulo_propiedad',
                            DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(tbl_propiedad.piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(tbl_propiedad.puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
              'reporta.nombre_usuario as nombre_inquilino'
            );

        if ($request->categoria) {
            $query->where('categoria_incidencia', $request->categoria);
        }
        if ($request->prioridad) {
            $query->where('prioridad_incidencia', $request->prioridad);
        }
        if ($request->estado) {
            $query->where('tbl_incidencia.estado_incidencia',
              $request->estado);
        }
        if ($request->q) {
            $query->where('titulo_incidencia','like',
              '%' . $request->q . '%');
        }

        $incidencias = $query
            ->orderBy('tbl_incidencia.creado_incidencia','desc')
            ->get();

        return response()->json([
            'incidencias' => $incidencias,
            'total' => $incidencias->count()
        ]);
    }

    public function crear(Request $request)
    {
        DB::beginTransaction();
        try {
            $idAdmin = DB::table('tbl_usuario')
                ->where('email_usuario','admin@spotstay.com')
                ->value('id_usuario');

            $idIncidencia = DB::table('tbl_incidencia')->insertGetId([
                'id_propiedad_fk' => $request->id_propiedad,
                'id_reporta_fk' => $request->id_inquilino,
                'titulo_incidencia' => $request->titulo,
                'descripcion_incidencia' => $request->descripcion,
                'categoria_incidencia' => $request->categoria,
                'prioridad_incidencia' => $request->prioridad,
                'estado_incidencia' => 'abierta',
                'creado_incidencia' => Carbon::now(),
                'actualizado_incidencia' => Carbon::now()
            ]);

            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $idIncidencia,
                'id_usuario_fk' => $idAdmin,
                'comentario_historial' => 'Incidencia creada por administrador',
                'cambio_estado_historial' => 'abierta',
                'creado_historial' => Carbon::now(),
                'actualizado_historial' => Carbon::now()
            ]);

            DB::commit();
            return response()->json(['success' => true, 'id' => $idIncidencia]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
