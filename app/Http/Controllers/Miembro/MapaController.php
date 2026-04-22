<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapaController extends Controller
{
    public function propiedades(Request $request)
    {
        $query = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'direccion_propiedad',
                'ciudad_propiedad',
                'latitud_propiedad',
                'longitud_propiedad',
                'precio_propiedad',
                'tipo_propiedad',
                'habitaciones_propiedad',
                'metros_cuadrados_propiedad',
                'estado_propiedad'
            )
            ->where('estado_propiedad', 'publicada')
            ->whereNotNull('latitud_propiedad')
            ->whereNotNull('longitud_propiedad');

        // Filtro por limites del mapa
        if (
            $request->filled('lat_min') &&
            $request->filled('lat_max') &&
            $request->filled('lng_min') &&
            $request->filled('lng_max')
        ) {
            $query->whereBetween('latitud_propiedad', [(float) $request->lat_min, (float) $request->lat_max]);
            $query->whereBetween('longitud_propiedad', [(float) $request->lng_min, (float) $request->lng_max]);
        }

        // Filtro por precio
        if ($request->filled('precio_minimo')) {
            $query->where('precio_propiedad', '>=', (float) $request->precio_minimo);
        }

        if ($request->filled('precio_maximo')) {
            $query->where('precio_propiedad', '<=', (float) $request->precio_maximo);
        }

        // Filtro por tipo de inmueble
        if ($request->filled('tipo_inmueble')) {
            $query->where('tipo_propiedad', $request->tipo_inmueble);
        }

        // Filtro por habitaciones
        if ($request->filled('habitaciones')) {
            $query->where('habitaciones_propiedad', trim((string) $request->habitaciones));
        }

        // Filtro por metros cuadrados
        if ($request->filled('metros_minimo')) {
            $query->where('metros_cuadrados_propiedad', '>=', (int) $request->metros_minimo);
        }

        if ($request->filled('metros_maximo')) {
            $query->where('metros_cuadrados_propiedad', '<=', (int) $request->metros_maximo);
        }

        $propiedades = $query->orderByDesc('id_propiedad')->limit(300)->get();

        return response()->json($propiedades);
    }
}
