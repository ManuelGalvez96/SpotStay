<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaArticulo;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::withCount('articulos')
            ->orderBy('orden')
            ->get();

        $nextOrden = (CategoriaArticulo::max('orden') ?? 0) + 1;

        return view('admin.asesoria', compact('categorias', 'nextOrden'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:tbl_asesoria_categoria,slug',
            'icono'  => 'required|string|max:50',
            'orden'  => 'required|integer|min:1',
        ]);

        CategoriaArticulo::where('orden', '>=', $validated['orden'])
            ->increment('orden');

        $categoria = CategoriaArticulo::create([
            'nombre' => $validated['nombre'],
            'slug'   => $validated['slug'],
            'icono'  => $validated['icono'],
            'orden'  => $validated['orden'],
            'estado' => false,
        ]);

        return response()->json([
            'message'   => 'Categoría creada correctamente.',
            'categoria' => $categoria,
        ]);
    }

    public function toggleEstado($id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);
        $categoria->estado = !$categoria->estado;
        $categoria->save();

        return response()->json([
            'success' => true,
            'message' => $categoria->estado ? 'Categoría activada.' : 'Categoría desactivada.',
        ]);
    }
}
