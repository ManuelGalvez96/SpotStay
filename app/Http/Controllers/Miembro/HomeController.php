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
        $usuario = Auth::user();

        // Lógica de usuario
        $nombreUsuario = $usuario 
            ? ($usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '') 
            : '';
        $tieneFoto = $usuario && !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';
        
        // Lógica de inquilino (botón Gestionar)
        $esInquilino = $usuario
            ? DB::table('tbl_alquiler')
                ->where('id_inquilino_fk', $usuario->id_usuario)
                ->where('estado_alquiler', 'activo')
                ->exists()
            : false;

        $ciudades = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'publicada')
            ->whereNotNull('ciudad_propiedad')
            ->distinct()
            ->orderBy('ciudad_propiedad')
            ->pluck('ciudad_propiedad');

        // Busca propiedades publicadas y aplica filtros del panel
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
            $query->where('habitaciones_propiedad', trim((string) $request->habitaciones));
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

        foreach (['amueblado', 'terraza', 'piscina', 'garaje', 'ascensor', 'aire_acondicionado', 'calefaccion', 'trastero'] as $campoBooleano) {
            if ($request->filled($campoBooleano)) {
                $query->where($campoBooleano . '_propiedad', (int) $request->$campoBooleano);
            }
        }

        $propiedades = $query->orderByDesc('id_propiedad')->get();

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

        return view('miembro.inicio', [
            'propiedades' => $propiedades,
            'totalPropiedades' => count($propiedades),
            'ciudades' => $ciudades,
            'nombreUsuario' => $nombreUsuario,
            'tieneFoto' => $tieneFoto,
            'fotoUsuario' => $fotoUsuario,
            'inicialUsuario' => $inicialUsuario,
            'esInquilino' => $esInquilino
        ]);
    }
}
