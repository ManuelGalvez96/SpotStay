<?php

namespace App\Http\Controllers;

use App\Models\CategoriaArticulo;
use Illuminate\Http\Request;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::with(['articulos' => function ($q) {
            $q->where('estado', 1);
        }])
            ->where('estado', 1)
            ->orderBy('orden')
            ->get();

        if (request()->routeIs('gestor.*')) {
            return view('asesoria.gestor', compact('categorias'));
        }

        if (request()->routeIs('arrendador.*')) {
            return view('asesoria.arrendador', compact('categorias'));
        }

        return view('asesoria.miembro', compact('categorias'));
    }
}
