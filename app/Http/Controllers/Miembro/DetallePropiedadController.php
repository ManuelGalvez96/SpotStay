<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DetallePropiedadController extends Controller
{
    public function index($id)
    {
        // Busca la propiedad por su ID
        $propiedad = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'direccion_propiedad',
                'ciudad_propiedad',
                'descripcion_propiedad',
                'precio_propiedad',
                'estado_propiedad'
            )
            ->where('id_propiedad', $id)
            ->first();

        // Carga hasta 5 fotos de la propiedad para el collage del detalle.
        $fotosPropiedad = DB::table('tbl_fotos')
            ->select('ruta_foto')
            ->where('id_propiedad_fk', $id)
            ->limit(5)
            ->get();

        return view('miembro.detalle_propiedad', compact('id', 'propiedad', 'fotosPropiedad'));
    }
    public function cargarFotos($id)
    {
        $fotos = DB::table('tbl_fotos')
            ->select('ruta_foto')
            ->where('id_propiedad_fk', $id)
            ->get();
        return response()->json($fotos);
    }
}
