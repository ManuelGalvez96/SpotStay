<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
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
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'ciudad_propiedad',
                'precio_propiedad',
                'estado_propiedad'
            )
            ->orderByDesc('id_propiedad')
            // ->limit(6)
            ->get();

        return view('miembro.inicio', [
            'propiedades' => $propiedades,
        ]);
    }
}
