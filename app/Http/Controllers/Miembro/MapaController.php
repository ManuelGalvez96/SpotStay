<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MapaController extends Controller
{
    /**
     * Muestra la vista del mapa de búsqueda de propiedades.
     *
     * Consulta las ciudades únicas de propiedades publicadas
     * para alimentar el dropdown de filtro por ciudad.
     */
    public function index(Request $request)
    {
        $ciudades = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->whereNotNull('ciudad_propiedad')
            ->distinct()
            ->orderBy('ciudad_propiedad')
            ->pluck('ciudad_propiedad');

        return view('miembro.mapa', [
            'ciudades' => $ciudades
        ]);
    }

    /**
     * API: Retorna propiedades publicadas como JSON para pintar marcadores en el mapa.
     *
     * Filtros aplicables (todos opcionales):
     *   - lat_min/lat_max/lng_min/lng_max: límites visibles del mapa (bounds)
     *   - precio_minimo/precio_maximo: rango de precio
     *   - tipo_inmueble: tipo de propiedad (piso, casa, etc.)
     *   - habitaciones: número exacto de habitaciones
     *   - ciudad: ciudad exacta
     *   - banos: número de baños (4+ trata como >= 4)
     *   - metros_minimo/metros_maximo: rango de metros cuadrados
     *   - extras booleanos: amueblado, terraza, piscina, garaje, ascensor,
     *     aire_acondicionado, calefaccion, trastero
     *
     * Retorna máximo 300 propiedades ordenadas por ID descendente.
     */
    public function propiedades(Request $request)
    {
        $query = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                'calle_propiedad',
                'numero_propiedad',
                'piso_propiedad',
                'puerta_propiedad',
                'ciudad_propiedad',
                'latitud_propiedad',
                'longitud_propiedad',
                'precio_propiedad',
                'tipo_propiedad',
                'habitaciones_propiedad',
                'metros_cuadrados_propiedad',
                'estado_propiedad'
            )
            ->selectRaw("TRIM(CONCAT_WS(', ', CONCAT_WS(' ', calle_propiedad, numero_propiedad), NULLIF(CONCAT('Piso ', piso_propiedad), 'Piso '), NULLIF(CONCAT('Puerta ', puerta_propiedad), 'Puerta '))) as direccion_propiedad")
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

        if ($request->filled('ciudad')) {
            $query->where('ciudad_propiedad', $request->ciudad);
        }

        if ($request->filled('banos')) {
            if ($request->banos === '4+') {
                $query->where('banos_propiedad', '>=', 4);
            } else {
                $query->where('banos_propiedad', (int) $request->banos);
            }
        }

        // Filtro por metros cuadrados
        if ($request->filled('metros_minimo')) {
            $query->where('metros_cuadrados_propiedad', '>=', (int) $request->metros_minimo);
        }

        if ($request->filled('metros_maximo')) {
            $query->where('metros_cuadrados_propiedad', '<=', (int) $request->metros_maximo);
        }

        foreach (['amueblado', 'terraza', 'piscina', 'garaje', 'ascensor', 'aire_acondicionado', 'calefaccion', 'trastero'] as $campoBooleano) {
            if ($request->filled($campoBooleano)) {
                $query->where($campoBooleano . '_propiedad', (int) $request->$campoBooleano);
            }
        }

        $propiedades = $query->orderByDesc('id_propiedad')->limit(300)->get();

        return response()->json($propiedades);
    }
}
