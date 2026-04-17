<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Busca las propiedades y ordena por fecha de creación (más recientes primero)
        $propiedades = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'direccion_propiedad',
                'ciudad_propiedad',
                'precio_propiedad',
                'estado_propiedad'
            )
            ->where('estado_propiedad', 'publicada')
            ->orderByDesc('id_propiedad')
            // ->limit(6)
            ->get();

        return view('miembro.inicio', [
            'propiedades' => $propiedades,
        ]);
    }
}
