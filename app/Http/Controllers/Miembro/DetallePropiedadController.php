<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DetallePropiedadController extends Controller
{
    public function show($id)
    {
        return $this->index($id);
    }

    public function index($id)
    {
        // Busca la propiedad por su ID
        $propiedad = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', 
                    TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)), 
                    NULLIF(CONCAT('Piso ', NULLIF(piso_propiedad, '')), 'Piso '), 
                    NULLIF(CONCAT('Puerta ', NULLIF(puerta_propiedad, '')), 'Puerta ')
                )) as direccion_propiedad"),
                'ciudad_propiedad',
                'latitud_propiedad',
                'longitud_propiedad',
                'descripcion_propiedad',
                'precio_propiedad',
                'tipo_propiedad',
                'habitaciones_propiedad',
                'metros_cuadrados_propiedad',
                'estado_propiedad',
                'id_arrendador_fk'
            )
            ->where('id_propiedad', $id)
            ->first();

        // Carga hasta 5 fotos de la propiedad para el collage del detalle.
        $fotosPropiedad = DB::table('tbl_fotos')
            ->select('ruta_foto')
            ->where('id_propiedad_fk', $id)
            ->limit(5)
            ->get();

        // Busca el arrendador de la propiedad
        $arrendador = DB::table('tbl_usuario')
            ->select('nombre_usuario', 'email_usuario', 'telefono_usuario')
            ->where('id_usuario', $propiedad->id_arrendador_fk)
            ->first();

        return view('miembro.detalle_propiedad', compact('id', 'propiedad', 'fotosPropiedad', 'arrendador'));
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
