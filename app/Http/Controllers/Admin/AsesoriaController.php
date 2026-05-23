<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaArticulo;

class AsesoriaController extends Controller
{
    public function index()
    {
        $categorias = CategoriaArticulo::withCount('articulos')
            ->orderBy('orden')
            ->get();

        return view('admin.asesoria', compact('categorias'));
    }
}
