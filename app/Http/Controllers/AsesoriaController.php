<?php

namespace App\Http\Controllers;

use App\Models\ArticuloAsesoria;
use App\Models\CategoriaArticulo;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::withCount('articulos')
            ->where('estado', 1)
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
