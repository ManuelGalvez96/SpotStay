<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
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

        // Busca las propiedades publicitadas
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
            ->get();

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
