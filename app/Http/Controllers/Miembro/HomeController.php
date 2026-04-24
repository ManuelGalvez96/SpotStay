<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();

        // Lógica de usuario
        $nombreUsuario = $usuario 
            ? ($usuario->name ?? $usuario->nombre_usuario ?? $usuario->email ?? '') 
            : '';
        $tieneFoto = $usuario && !empty($usuario->foto_usuario);
        $fotoUsuario = $tieneFoto ? asset('storage/' . $usuario->foto_usuario) : '';
        $inicialUsuario = $nombreUsuario !== '' ? strtoupper(substr($nombreUsuario, 0, 1)) : '';
        
        // Lógica de inquilino (botón Gestionar)
        $esInquilino = $usuario && $usuario->alquileres()->where('estado_alquiler', 'activo')->exists();

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

        if ($request->filled('buscador')) {
            $texto = trim((string) $request->buscador);
            $query->where(function ($subQuery) use ($texto) {
                $subQuery->where('ciudad_propiedad', 'like', '%' . $texto . '%')
                    ->orWhere('calle_propiedad', 'like', '%' . $texto . '%')
                    ->orWhere('titulo_propiedad', 'like', '%' . $texto . '%');
            });
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

        $propiedades = $query->orderByDesc('id_propiedad')->get();

        return view('miembro.inicio', [
            'propiedades' => $propiedades,
            'totalPropiedades' => count($propiedades),
            'nombreUsuario' => $nombreUsuario,
            'tieneFoto' => $tieneFoto,
            'fotoUsuario' => $fotoUsuario,
            'inicialUsuario' => $inicialUsuario,
            'esInquilino' => $esInquilino
        ]);
    }
}
