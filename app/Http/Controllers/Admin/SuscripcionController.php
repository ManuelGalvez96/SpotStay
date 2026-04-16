<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SuscripcionController extends Controller
{
    /**
     * Listar suscripciones con paginación
     */
    public function index()
    {
        $suscripciones = DB::table('tbl_suscripcion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_suscripcion.id_usuario_fk')
            ->select(
              'tbl_suscripcion.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario',
              DB::raw('(SELECT COUNT(*) FROM tbl_propiedad
                WHERE id_arrendador_fk = tbl_suscripcion.id_usuario_fk
                AND estado_propiedad != "inactiva")
                as propiedades_usadas')
            )
            ->orderBy('tbl_suscripcion.creado_suscripcion','desc')
            ->paginate(10);

        $totalActivas = DB::table('tbl_suscripcion')
            ->where('estado_suscripcion','activa')->count();
        $totalPro = DB::table('tbl_suscripcion')
            ->where('plan_suscripcion','pro')
            ->where('estado_suscripcion','activa')->count();
        $totalBasico = DB::table('tbl_suscripcion')
            ->where('plan_suscripcion','basico')
            ->where('estado_suscripcion','activa')->count();
        $totalGratuito = DB::table('tbl_suscripcion')
            ->where('plan_suscripcion','gratuito')
            ->where('estado_suscripcion','activa')->count();
        $totalExpiradas = DB::table('tbl_suscripcion')
            ->where('estado_suscripcion','expirada')->count();

        $totalActivas2 = $totalPro + $totalBasico + $totalGratuito;
        $pctPro = $totalActivas2 > 0
            ? round($totalPro / $totalActivas2 * 100) : 0;
        $pctBasico = $totalActivas2 > 0
            ? round($totalBasico / $totalActivas2 * 100) : 0;
        $pctGratuito = $totalActivas2 > 0
            ? round($totalGratuito / $totalActivas2 * 100) : 0;

        $precioPro = 29.99;
        $precioBasico = 9.99;
        $ingresosMes = ($totalPro * $precioPro)
                     + ($totalBasico * $precioBasico);

        $proximasExpirar = DB::table('tbl_suscripcion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_suscripcion.id_usuario_fk')
            ->where(function($q) {
                $q->where('estado_suscripcion','expirada')
                  ->orWhere(function($q2) {
                      $q2->where('estado_suscripcion','activa')
                         ->whereNotNull('fin_suscripcion')
                         ->where('fin_suscripcion','<=',
                           Carbon::now()->addDays(30));
                  });
            })
            ->select(
              'tbl_suscripcion.*',
              'tbl_usuario.nombre_usuario'
            )
            ->orderBy('tbl_suscripcion.fin_suscripcion','asc')
            ->limit(5)
            ->get();

        return view('admin.suscripciones', compact(
            'suscripciones',
            'totalActivas',
            'totalPro',
            'totalBasico',
            'totalGratuito',
            'totalExpiradas',
            'pctPro',
            'pctBasico',
            'pctGratuito',
            'precioPro',
            'precioBasico',
            'ingresosMes',
            'proximasExpirar'
        ));
    }

    /**
     * Obtener detalle de suscripción (JSON)
     * Solo lectura — sin transacción
     */
    public function show($id)
    {
        $sus = DB::table('tbl_suscripcion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_suscripcion.id_usuario_fk')
            ->where('tbl_suscripcion.id_suscripcion',$id)
            ->select(
              'tbl_suscripcion.*',
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario',
              'tbl_usuario.telefono_usuario'
            )
            ->first();

        if (!$sus) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $propiedades = DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $sus->id_usuario_fk)
            ->where('estado_propiedad','!=','inactiva')
            ->select('id_propiedad','titulo_propiedad',
              'ciudad_propiedad','estado_propiedad')
            ->get();

        return response()->json([
            'suscripcion' => $sus,
            'propiedades' => $propiedades
        ]);
    }

    /**
     * Filtrar suscripciones (read-only)
     */
    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_suscripcion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_suscripcion.id_usuario_fk');

        if ($request->plan) {
            $query->where('plan_suscripcion',$request->plan);
        }
        if ($request->estado) {
            $query->where('estado_suscripcion',$request->estado);
        }
        if ($request->q) {
            $query->where('tbl_usuario.nombre_usuario','like',
              '%'.$request->q.'%');
        }

        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    /**
     * Editar suscripción (solo 1 tabla)
     */
    public function editar(Request $request, $id)
    {
        $datos = [
            'plan_suscripcion'        => $request->plan,
            'actualizado_suscripcion' => Carbon::now()
        ];

        if ($request->fecha_inicio) {
            $datos['inicio_suscripcion'] = $request->fecha_inicio;
        }
        if ($request->fecha_fin) {
            $datos['fin_suscripcion'] = $request->fecha_fin;
        }

        DB::table('tbl_suscripcion')
            ->where('id_suscripcion',$id)
            ->update($datos);

        return response()->json(['success' => true]);
    }

    /**
     * Cancelar suscripción (solo 1 tabla)
     */
    public function cancelar($id)
    {
        DB::table('tbl_suscripcion')
            ->where('id_suscripcion',$id)
            ->update([
                'estado_suscripcion'      => 'cancelada',
                'actualizado_suscripcion' => Carbon::now()
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Exportar suscripciones a CSV
     */
    public function exportar()
    {
        $suscripciones = DB::table('tbl_suscripcion')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario','=',
              'tbl_suscripcion.id_usuario_fk')
            ->select(
              'tbl_usuario.nombre_usuario',
              'tbl_usuario.email_usuario',
              'tbl_suscripcion.plan_suscripcion',
              'tbl_suscripcion.estado_suscripcion',
              'tbl_suscripcion.inicio_suscripcion',
              'tbl_suscripcion.fin_suscripcion'
            )->get();

        $csv = "Nombre,Email,Plan,Estado,Inicio,Fin\n";
        foreach ($suscripciones as $s) {
            $csv .= implode(',', [
                $s->nombre_usuario,
                $s->email_usuario,
                $s->plan_suscripcion,
                $s->estado_suscripcion,
                $s->inicio_suscripcion ?? '',
                $s->fin_suscripcion ?? ''
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' =>
              'attachment; filename="suscripciones.csv"'
        ]);
    }
}
