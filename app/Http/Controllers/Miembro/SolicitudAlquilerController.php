<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudAlquilerController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'fecha_entrada' => ['required', 'date', 'after_or_equal:today'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
        ]);

        $propiedadExiste = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->exists();

        if (!$propiedadExiste) {
            return redirect()->back()->with('error', 'La propiedad no existe.');
        }

        Solicitud::create([
            'propiedad_id' => $id,
            'usuario_id' => Auth::id(),
            'mensaje' => $request->input('mensaje'),
            'fecha_entrada' => $request->input('fecha_entrada'),
            'estado' => 'pendiente',
        ]);

        return redirect()->back()->with('success', 'Tu solicitud de alquiler se envió correctamente.');
    }
}
