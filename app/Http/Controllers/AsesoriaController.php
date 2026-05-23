<?php

namespace App\Http\Controllers;

use App\Models\ArticuloAsesoria;
use App\Models\CategoriaArticulo;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::withCount('articulos')
            ->where('estado', 1)
            ->has('articulos')
            ->orderBy('orden')
            ->get();

        $faqs = ArticuloAsesoria::with('categoria')
            ->where('destacado', true)
            ->where('estado', 1)
            ->orderBy('orden_faq')
            ->get();

        return view('asesoria.index', [
            'layout' => $this->layout(),
            'routePrefix' => $this->routePrefix(),
            'categorias' => $categorias,
            'faqs' => $faqs,
        ]);
    }

    public function categoria($slug)
    {
        $categoria = CategoriaArticulo::with(['articulos' => function ($q) {
            $q->where('estado', 1);
        }])
            ->where('slug', $slug)
            ->where('estado', 1)
            ->firstOrFail();

        return view('asesoria.categoria', [
            'layout' => $this->layout(),
            'routePrefix' => $this->routePrefix(),
            'categoria' => $categoria,
        ]);
    }

    public function buscar(Request $request)
    {
        $q = $request->input('q');

        if (!$q || strlen(trim($q)) < 1) {
            return response()->json([]);
        }

        $articulos = ArticuloAsesoria::with('categoria')
            ->where('estado', 1)
            ->where('titulo', 'like', '%' . trim($q) . '%')
            ->limit(10)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'titulo' => $a->titulo,
                    'categoria_nombre' => $a->categoria?->nombre,
                    'categoria_slug' => $a->categoria?->slug,
                    'url' => $a->categoria
                        ? route($this->routePrefix() . '.asesoria.categoria', $a->categoria->slug) . '#art-' . $a->id
                        : '#',
                ];
            });

        return response()->json($articulos);
    }

    private function layout()
    {
        if (request()->routeIs('gestor.*')) return 'layouts.gestor';
        if (request()->routeIs('arrendador.*')) return 'layouts.arrendador';
        return 'layouts.miembro';
    }

    private function routePrefix()
    {
        if (request()->routeIs('gestor.*')) return 'gestor';
        if (request()->routeIs('arrendador.*')) return 'arrendador';
        if (request()->routeIs('inquilino.*')) return 'inquilino';
        return 'miembro';
    }
}
