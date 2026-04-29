<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // El usuario se obtiene del Auth global si es necesario para lógica interna
        $usuario = Auth::user();

        // Obtener ciudades para el filtro
        $ciudades = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->whereNotNull('ciudad_propiedad')
            ->distinct()
            ->orderBy('ciudad_propiedad')
            ->pluck('ciudad_propiedad');

        // Consulta base para propiedades
        $query = DB::table('tbl_propiedad')
            ->select(
                'id_propiedad',
                'titulo_propiedad',
                DB::raw("TRIM(CONCAT_WS(', ', TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)), NULLIF(CONCAT('Piso ', NULLIF(piso_propiedad, '')), 'Piso '), NULLIF(CONCAT('Puerta ', NULLIF(puerta_propiedad, '')), 'Puerta '))) as direccion_propiedad"),
                'ciudad_propiedad',
                'precio_propiedad',
                'estado_propiedad'
            )
            ->where('estado_propiedad', 'publicada');

        // Filtros dinámicos
        if ($request->filled('ciudad')) {
            $query->where('ciudad_propiedad', $request->ciudad);
        }

        if ($request->filled('precio_minimo')) {
            $query->where('precio_propiedad', '>=', (float) $request->precio_minimo);
        }

        if ($request->filled('precio_maximo')) {
            $query->where('precio_propiedad', '<=', (float) $request->precio_maximo);
        }

        if ($request->filled('tipo_inmueble')) {
            $query->where('tipo_propiedad', (string) $request->tipo_inmueble);
        }

        if ($request->filled('habitaciones')) {
            if ($request->habitaciones === '4+') {
                $query->where('habitaciones_propiedad', '>=', 4);
            } else {
                $query->where('habitaciones_propiedad', (int) $request->habitaciones);
            }
        }

        if ($request->filled('banos')) {
            if ($request->banos === '4+') {
                $query->where('banos_propiedad', '>=', 4);
            } else {
                $query->where('banos_propiedad', (int) $request->banos);
            }
        }

        if ($request->filled('metros_minimo')) {
            $query->where('metros_cuadrados_propiedad', '>=', (int) $request->metros_minimo);
        }

        if ($request->filled('metros_maximo')) {
            $query->where('metros_cuadrados_propiedad', '<=', (int) $request->metros_maximo);
        }

        // Filtros booleanos (servicios)
        $servicios = [
            'amueblado', 'terraza', 'piscina', 'garaje', 
            'ascensor', 'aire_acondicionado', 'calefaccion', 'trastero'
        ];

        foreach ($servicios as $servicio) {
            if ($request->filled($servicio)) {
                $query->where($servicio . '_propiedad', (int) $request->$servicio);
            }
        }

        $propiedades = $query->orderByDesc('id_propiedad')->get();

        // Respuesta para AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Listado cargado correctamente.',
                'data' => [
                    'propiedades' => $propiedades,
                    'total' => count($propiedades)
                ]
            ], 200);
        }

        // Carga de la vista normal (Ya no pasamos variables de usuario, vienen del ViewComposer)
        return view('miembro.inicio', [
            'propiedades' => $propiedades,
            'totalPropiedades' => count($propiedades),
            'ciudades' => $ciudades
        ]);
    }
}
