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
        // Busca la propiedad utilizando el modelo Eloquent para habilitar accessors y relaciones
        $propiedad = \App\Models\Propiedad::with(['arrendador', 'fotos' => function($q) {
            $q->limit(5);
        }])->find($id);

        if (!$propiedad) {
            return abort(404, 'Propiedad no encontrada');
        }

        $fotosPropiedad = $propiedad->fotos;
        $arrendador = $propiedad->arrendador;

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
