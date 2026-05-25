<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticuloAsesoria;
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
        $query = CategoriaArticulo::withCount([
            'articulos',
            'articulos as destacados_count' => function ($q) {
                $q->where('destacado', true);
            }
        ]);

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

        $categoria->loadCount(['articulos as destacados_count' => function ($q) {
            $q->where('destacado', true);
        }]);

        return response()->json([
            'success'          => true,
            'message'          => $categoria->estado ? 'Categoría activada.' : 'Categoría desactivada.',
            'estado'           => $categoria->estado,
            'destacados_count' => $categoria->destacados_count,
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

        $oldOrden = $categoria->orden;
        $categoria->delete();

        CategoriaArticulo::where('orden', '>', $oldOrden)->decrement('orden');

        return response()->json([
            'success' => true,
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }

    /* ============================================
       ARTÍCULOS
       ============================================ */

    public function articulos()
    {
        $categorias = CategoriaArticulo::orderBy('nombre')->get(['id', 'nombre', 'estado']);

        return view('admin.asesoria-articulos', compact('categorias'));
    }

    public function filtrarArticulos(Request $request)
    {
        $query = ArticuloAsesoria::with('categoria:id,nombre,estado');

        if ($request->filled('q')) {
            $query->where('tbl_asesoria_articulo.titulo', 'like', '%' . $request->q . '%');
        }

        $estado = $request->input('estado');
        if ($estado !== null && $estado !== '') {
            $query->where('tbl_asesoria_articulo.estado', (int)$estado);
        }

        $categoriaId = $request->input('categoria');
        if ($categoriaId !== null && $categoriaId !== '') {
            $query->where('tbl_asesoria_articulo.id_categoria_fk', (int)$categoriaId);
        }

        $destacadoFiltro = $request->input('destacado_filtro');
        if ($destacadoFiltro !== null && $destacadoFiltro !== '') {
            $query->where('tbl_asesoria_articulo.destacado', (int)$destacadoFiltro);
        }

        $sort = $request->input('sort', 'categoria');
        $direction = $request->input('direction', 'asc');

        $allowedSorts = ['titulo', 'slug', 'estado', 'destacado', 'orden_faq'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy('tbl_asesoria_articulo.' . $sort, $direction === 'desc' ? 'desc' : 'asc');
        } elseif ($sort === 'categoria') {
            $query->leftJoin('tbl_asesoria_categoria', 'tbl_asesoria_articulo.id_categoria_fk', '=', 'tbl_asesoria_categoria.id')
                  ->select('tbl_asesoria_articulo.*', 'tbl_asesoria_categoria.nombre as categoria_nombre')
                  ->orderBy('tbl_asesoria_categoria.nombre', $direction === 'desc' ? 'desc' : 'asc')
                  ->orderBy('tbl_asesoria_articulo.orden', 'asc');
        } else {
            $query->orderBy('orden');
        }

        $perPage = $request->input('per_page', 10);
        if ($perPage == 0) {
            $perPage = $query->count();
            if ($perPage < 1) $perPage = 1;
        }

        $articulos = $query->paginate($perPage);

        return response()->json($articulos);
    }

    public function storeArticulo(Request $request)
    {
        $validated = $request->validate([
            'id_categoria_fk' => 'required|integer|exists:tbl_asesoria_categoria,id',
            'titulo'          => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:tbl_asesoria_articulo,slug',
            'contenido'       => 'required|string',
            'orden'           => 'required|integer|min:1',
            'destacado'       => 'sometimes|boolean',
            'orden_faq'       => 'nullable|integer|min:1',
        ]);

        ArticuloAsesoria::where('orden', '>=', $validated['orden'])
            ->where('id_categoria_fk', $validated['id_categoria_fk'])
            ->increment('orden');

        $articulo = ArticuloAsesoria::create([
            'id_categoria_fk' => $validated['id_categoria_fk'],
            'titulo'          => $validated['titulo'],
            'slug'            => $validated['slug'],
            'contenido'       => $validated['contenido'],
            'orden'           => $validated['orden'],
            'estado'          => false,
            'destacado'       => $request->boolean('destacado'),
            'orden_faq'       => $validated['orden_faq'],
        ]);

        return response()->json([
            'message'  => 'Artículo creado correctamente.',
            'articulo' => $articulo,
        ]);
    }

    public function toggleEstadoArticulo($id)
    {
        $articulo = ArticuloAsesoria::findOrFail($id);
        $articulo->estado = !$articulo->estado;
        $articulo->save();

        return response()->json([
            'success' => true,
            'message' => $articulo->estado ? 'Artículo activado.' : 'Artículo desactivado.',
        ]);
    }

    public function toggleDestacadoArticulo($id)
    {
        $articulo = ArticuloAsesoria::findOrFail($id);
        $oldOrdenFaq = $articulo->orden_faq;

        if (!$articulo->destacado) {
            $maxOrdenFaq = ArticuloAsesoria::max('orden_faq') ?? 0;
            $articulo->orden_faq = $maxOrdenFaq + 1;
            $articulo->destacado = true;
        } else {
            $articulo->orden_faq = null;
            $articulo->destacado = false;
            if ($oldOrdenFaq !== null) {
                ArticuloAsesoria::where('orden_faq', '>', $oldOrdenFaq)
                    ->decrement('orden_faq');
            }
        }

        $articulo->save();

        $affected[] = ['id' => $articulo->id, 'orden_faq' => $articulo->orden_faq];
        if (!$articulo->destacado && $oldOrdenFaq !== null) {
            $shifted = ArticuloAsesoria::whereNotNull('orden_faq')
                ->where('orden_faq', '>=', $oldOrdenFaq)
                ->get(['id', 'orden_faq']);
            foreach ($shifted as $s) {
                $affected[] = ['id' => $s->id, 'orden_faq' => $s->orden_faq];
            }
        }

        return response()->json([
            'success'    => true,
            'destacado'  => $articulo->destacado,
            'orden_faq'  => $articulo->orden_faq,
            'affected'   => $affected,
            'message'    => $articulo->destacado ? 'Artículo marcado como destacado.' : 'Artículo desmarcado como destacado.',
        ]);
    }

    public function editArticulo($id)
    {
        $articulo = ArticuloAsesoria::with('categoria:id,nombre')->findOrFail($id);
        $maxOrden = ArticuloAsesoria::where('id_categoria_fk', $articulo->id_categoria_fk)->max('orden') ?? 0;
        $categorias = CategoriaArticulo::orderBy('nombre')->get(['id', 'nombre']);

        return response()->json([
            'articulo'   => $articulo,
            'maxOrden'   => $maxOrden,
            'categorias' => $categorias,
        ]);
    }

    public function updateArticulo(Request $request, $id)
    {
        $articulo = ArticuloAsesoria::findOrFail($id);

        $validated = $request->validate([
            'id_categoria_fk' => 'required|integer|exists:tbl_asesoria_categoria,id',
            'titulo'          => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:tbl_asesoria_articulo,slug,' . $id,
            'contenido'       => 'required|string',
            'orden'           => 'required|integer|min:1',
            'destacado'       => 'sometimes|boolean',
            'orden_faq'       => 'nullable|integer|min:1',
        ]);

        $oldCategoria = $articulo->id_categoria_fk;
        $oldOrden = $articulo->orden;
        $newOrden = $validated['orden'];
        $newCategoria = $validated['id_categoria_fk'];

        if ($oldCategoria == $newCategoria && $oldOrden !== $newOrden) {
            if ($newOrden < $oldOrden) {
                ArticuloAsesoria::where('id_categoria_fk', $newCategoria)
                    ->where('orden', '>=', $newOrden)
                    ->where('orden', '<', $oldOrden)
                    ->increment('orden');
            } else {
                ArticuloAsesoria::where('id_categoria_fk', $newCategoria)
                    ->where('orden', '<=', $newOrden)
                    ->where('orden', '>', $oldOrden)
                    ->decrement('orden');
            }
        }

        if ($oldCategoria != $newCategoria) {
            ArticuloAsesoria::where('id_categoria_fk', $oldCategoria)
                ->where('orden', '>', $oldOrden)
                ->decrement('orden');

            ArticuloAsesoria::where('id_categoria_fk', $newCategoria)
                ->where('orden', '>=', $newOrden)
                ->increment('orden');
        }

        $oldDestacado = $articulo->destacado;
        $oldOrdenFaq = $articulo->orden_faq;
        $newDestacado = $request->boolean('destacado');
        $newOrdenFaq = $validated['orden_faq'] ?? null;

        if ($oldDestacado && !$newDestacado) {
            $validated['destacado'] = false;
            $validated['orden_faq'] = null;
            if ($oldOrdenFaq !== null) {
                ArticuloAsesoria::where('orden_faq', '>', $oldOrdenFaq)->decrement('orden_faq');
            }
        } elseif (!$oldDestacado && $newDestacado) {
            $validated['destacado'] = true;
            if ($newOrdenFaq !== null) {
                ArticuloAsesoria::where('orden_faq', '>=', $newOrdenFaq)
                    ->where('id', '!=', $id)->increment('orden_faq');
            } else {
                $newOrdenFaq = (ArticuloAsesoria::max('orden_faq') ?? 0) + 1;
            }
            $validated['orden_faq'] = $newOrdenFaq;
        } elseif ($oldDestacado && $newDestacado && $oldOrdenFaq != $newOrdenFaq) {
            if ($newOrdenFaq > $oldOrdenFaq) {
                ArticuloAsesoria::where('orden_faq', '>', $oldOrdenFaq)
                    ->where('orden_faq', '<=', $newOrdenFaq)
                    ->where('id', '!=', $id)->decrement('orden_faq');
            } else {
                ArticuloAsesoria::where('orden_faq', '>=', $newOrdenFaq)
                    ->where('orden_faq', '<', $oldOrdenFaq)
                    ->where('id', '!=', $id)->increment('orden_faq');
            }
            $validated['orden_faq'] = $newOrdenFaq;
        }

        $articulo->update($validated);

        return response()->json([
            'message'  => 'Artículo actualizado correctamente.',
            'articulo' => $articulo,
        ]);
    }

    public function maxOrdenArticulo($categoriaId)
    {
        $max = ArticuloAsesoria::where('id_categoria_fk', $categoriaId)->max('orden') ?? 0;

        return response()->json(['max_orden' => (int)$max]);
    }

    public function maxOrdenFaq()
    {
        $max = ArticuloAsesoria::max('orden_faq') ?? 0;
        return response()->json(['max_orden_faq' => (int)$max]);
    }

    public function destroyArticulo($id)
    {
        $articulo = ArticuloAsesoria::findOrFail($id);
        $oldOrden = $articulo->orden;
        $oldOrdenFaq = $articulo->orden_faq;
        $oldCategoria = $articulo->id_categoria_fk;
        $articulo->delete();

        ArticuloAsesoria::where('id_categoria_fk', $oldCategoria)
            ->where('orden', '>', $oldOrden)
            ->decrement('orden');

        if ($oldOrdenFaq !== null) {
            ArticuloAsesoria::where('orden_faq', '>', $oldOrdenFaq)
                ->decrement('orden_faq');
        }

        return response()->json([
            'success' => true,
            'message' => 'Artículo eliminado correctamente.',
        ]);
    }
}
