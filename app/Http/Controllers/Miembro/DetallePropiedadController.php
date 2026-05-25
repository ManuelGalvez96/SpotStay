<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DetallePropiedadController extends Controller
{
    public function show($id)
    {
        return $this->index($id);
    }

    public function index($id)
    {
        $propiedad = \App\Models\Propiedad::with(['arrendador', 'fotos' => function($q) {
            $q->orderBy('es_principal_foto', 'desc')->limit(5);
        }])->find($id);

        if (!$propiedad) {
            return abort(404, 'Propiedad no encontrada');
        }

        $fotosPropiedad = $propiedad->fotos;
        $arrendador = $propiedad->arrendador;

        return view('miembro.detalle_propiedad', [
            'id' => $id,
            'propiedad' => $propiedad,
            'fotosPropiedad' => $fotosPropiedad,
            'arrendador' => $arrendador
        ]);
    }

    public function cargarFotos($id)
    {
        $fotos = DB::table('tbl_fotos')
            ->select('ruta_foto')
            ->where('id_propiedad_fk', $id)
            ->orderBy('es_principal_foto', 'desc')
            ->get();
        return response()->json($fotos);
    }
}
