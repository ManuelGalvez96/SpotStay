<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaArticulo;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    public function index()
    {
        $nextOrden = (CategoriaArticulo::max('orden') ?? 0) + 1;

        return view('admin.asesoria', compact('nextOrden'));
    }

    public function filtrar(Request $request)
    {
        $query = CategoriaArticulo::withCount('articulos');

        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%' . $request->q . '%');
        }

        $estado = $request->input('estado');
        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int)$estado);
        }

        $sort = $request->input('sort', 'orden');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['orden', 'nombre', 'slug', 'estado'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
        } elseif ($sort === 'articulos') {
            $query->orderBy('articulos_count', $direction === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('orden');
        }

        $perPage = $request->input('per_page', 10);
        if ($perPage == 0) {
            $perPage = $query->count();
            if ($perPage < 1) $perPage = 1;
        }

        $categorias = $query->paginate($perPage);

        return response()->json($categorias);
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

    public function edit($id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);
        $maxOrden = CategoriaArticulo::max('orden') ?? 0;

        return response()->json([
            'categoria' => $categoria,
            'maxOrden'  => $maxOrden,
        ]);
    }

    public function update(Request $request, $id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'slug'   => 'required|string|max:255|unique:tbl_asesoria_categoria,slug,' . $id,
            'icono'  => 'required|string|max:50',
            'orden'  => 'required|integer|min:1',
        ]);

        $oldOrden = $categoria->orden;
        $newOrden = $validated['orden'];

        if ($oldOrden !== $newOrden) {
            if ($newOrden < $oldOrden) {
                CategoriaArticulo::where('orden', '>=', $newOrden)
                    ->where('orden', '<', $oldOrden)
                    ->increment('orden');
            } else {
                CategoriaArticulo::where('orden', '<=', $newOrden)
                    ->where('orden', '>', $oldOrden)
                    ->decrement('orden');
            }
        }

        $categoria->update($validated);

        return response()->json([
            'message'   => 'Categoría actualizada correctamente.',
            'categoria' => $categoria,
        ]);
    }

    public function destroy($id)
    {
        $categoria = CategoriaArticulo::findOrFail($id);

        if ($categoria->articulos()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría porque tiene artículos asociados.',
            ], 409);
        }

        $categoria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }
}
