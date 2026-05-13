<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolicitudAlquilerController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'fecha_inicio_solicitud' => 'required|date',
            'mensaje_solicitud' => 'required|string|max:500',
        ]);

        $propiedad = DB::table('tbl_propiedad')
            ->where('id_propiedad', $id)
            ->first();

        if (!$propiedad) {
            return redirect()->back()->with('error', 'La propiedad no existe.');
        }

        $usuario = Auth::user();

        if (!$usuario) {
            return redirect('/login');
        }

        $existeAlquilerActivo = DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $id)
            ->whereIn('estado_alquiler', ['pendiente', 'activo'])
            ->exists();

        if ($existeAlquilerActivo) {
            return redirect()->back()->with('error', 'Esta propiedad ya tiene un alquiler activo o pendiente.');
        }

        $solicitudPendiente = DB::table('tbl_solicitud_alquiler')
            ->where('id_propiedad_fk', $id)
            ->where('id_usuario_fk', $usuario->id_usuario)
            ->where('estado_solicitud_alquiler', 'pendiente')
            ->exists();

        if ($solicitudPendiente) {
            return redirect()->back()->with('error', 'Ya tienes una solicitud pendiente para esta propiedad.');
        }

        try {
            DB::table('tbl_solicitud_alquiler')->insert([
                'id_propiedad_fk' => $id,
                'id_usuario_fk' => $usuario->id_usuario,
                'fecha_inicio_solicitud_alquiler' => $request->input('fecha_inicio_solicitud'),
                'mensaje_solicitud_alquiler' => $request->input('mensaje_solicitud'),
                'estado_solicitud_alquiler' => 'pendiente',
                'creado_solicitud_alquiler' => Carbon::now(),
                'actualizado_solicitud_alquiler' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo enviar la solicitud de alquiler.');
        }

        return redirect()->back()->with('success', 'Solicitud enviada correctamente.');
    }
}
