<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArticulo;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::withCount('articulos')
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();

        return view('asesoria.' . $this->rol(), compact('categorias'));
    }

    public function categoria($slug)
    {
        $categoria = CategoriaArticulo::with(['articulos' => function ($q) {
            $q->where('estado', 1);
        }])
            ->where('slug', $slug)
            ->where('estado', 1)
            ->firstOrFail();

        return view('asesoria.' . $this->rol() . '-categoria', compact('categoria'));
    }

    private function rol()
    {
        if (request()->routeIs('gestor.*')) return 'gestor';
        if (request()->routeIs('arrendador.*')) return 'arrendador';
        return 'miembro';
    }
}
