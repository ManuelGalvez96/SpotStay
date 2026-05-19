<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::where('estado_categoria', 'activa')->get();
        return view('admin.categorias', compact('categorias'));
    }

    public function crear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_categoria' => 'required|string|max:100|unique:tbl_categoria,nombre_categoria',
            'descripcion_categoria' => 'nullable|string|max:500',
        ], [
            'nombre_categoria.required' => 'El nombre de la categoría es obligatorio.',
            'nombre_categoria.unique' => 'Esta categoría ya existe.',
            'nombre_categoria.max' => 'El nombre no puede exceder 100 caracteres.',
            'descripcion_categoria.max' => 'La descripción no puede exceder 500 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $categoria = Categoria::create([
            'nombre_categoria' => $request->nombre_categoria,
            'descripcion_categoria' => $request->descripcion_categoria,
            'estado_categoria' => 'activa',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada correctamente.',
            'data' => $categoria,
        ], 201);
    }

    public function obtenerCategorias()
    {
        $categorias = Categoria::where('estado_categoria', 'activa')
            ->select('id_categoria', 'nombre_categoria')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias,
        ]);
    }

    public function editar(Request $request, $idCategoria)
    {
        $validator = Validator::make($request->all(), [
            'nombre_categoria' => 'required|string|max:100|unique:tbl_categoria,nombre_categoria,' . $idCategoria . ',id_categoria',
            'descripcion_categoria' => 'nullable|string|max:500',
            'estado_categoria' => 'required|in:activa,inactiva',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $categoria = Categoria::find($idCategoria);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        $categoria->update([
            'nombre_categoria' => $request->nombre_categoria,
            'descripcion_categoria' => $request->descripcion_categoria,
            'estado_categoria' => $request->estado_categoria,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada correctamente.',
            'data' => $categoria,
        ]);
    }

    public function eliminar($idCategoria)
    {
        $categoria = Categoria::find($idCategoria);

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada.',
            ], 404);
        }

        // Cambiar estado a inactiva en lugar de eliminar
        $categoria->update(['estado_categoria' => 'inactiva']);

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }
}
