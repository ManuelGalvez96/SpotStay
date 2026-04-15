<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PropiedadController extends Controller
{
    public function index()
    {
        $propiedades = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin(DB::raw('(SELECT id_propiedad_fk,
              COUNT(*) as total_inquilinos
              FROM tbl_alquiler WHERE estado_alquiler = "activo"
              GROUP BY id_propiedad_fk) as alq'),
              'alq.id_propiedad_fk', '=', 'tbl_propiedad.id_propiedad')
            ->select(
              'tbl_propiedad.*',
              'arrendador.nombre_usuario as nombre_arrendador',
              'alq.total_inquilinos'
            )
            ->orderBy('tbl_propiedad.creado_propiedad', 'desc')
            ->paginate(10);

        $totalPropiedades = DB::table('tbl_propiedad')->count();
        $alquiladas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'alquilada')->count();
        $publicadas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')->count();
        $inactivas = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'inactiva')->count();

        return view('admin.propiedades', compact(
            'propiedades', 'totalPropiedades',
            'alquiladas', 'publicadas', 'inactivas'));
    }

    public function filtrar(Request $request)
    {
        $query = DB::table('tbl_propiedad');

        if ($request->input('estado')) {
            $query->where('estado_propiedad', $request->input('estado'));
        }

        if ($request->input('ciudad')) {
            $query->where('ciudad_propiedad', $request->input('ciudad'));
        }

        if ($request->input('precioMin')) {
            $query->where('precio_propiedad', '>=', $request->input('precioMin'));
        }

        if ($request->input('precioMax')) {
            $query->where('precio_propiedad', '<=', $request->input('precioMax'));
        }

        $propiedades = $query->get();
        $total = $propiedades->count();

        return response()->json([
            'propiedades' => $propiedades,
            'total' => $total
        ]);
    }

    public function show($id)
    {
        $propiedad = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->leftJoin('tbl_usuario as gestor',
              'gestor.id_usuario', '=',
              'tbl_propiedad.id_gestor_fk')
            ->select(
              'tbl_propiedad.*',
              'arrendador.nombre_usuario as nombre_arrendador',
              'arrendador.email_usuario as email_arrendador',
              'gestor.nombre_usuario as nombre_gestor'
            )
            ->where('tbl_propiedad.id_propiedad', $id)
            ->first();

        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_usuario',
              'tbl_usuario.id_usuario', '=',
              'tbl_alquiler.id_inquilino_fk')
            ->where('id_propiedad_fk', $id)
            ->where('estado_alquiler', 'activo')
            ->select('tbl_alquiler.*', 'tbl_usuario.nombre_usuario')
            ->get();

        return response()->json([
            'propiedad' => $propiedad,
            'alquileres' => $alquileres
        ]);
    }

    public function desactivar($id)
    {
        DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->update(['estado_propiedad' => 'inactiva']);

        return response()->json(['success' => true]);
    }

    public function exportar()
    {
        $propiedades = DB::table('tbl_propiedad')
            ->join('tbl_usuario as arrendador',
              'arrendador.id_usuario', '=',
              'tbl_propiedad.id_arrendador_fk')
            ->select('tbl_propiedad.*', 'arrendador.nombre_usuario as nombre_arrendador')
            ->get();

        return response()->json($propiedades);
    }
}
