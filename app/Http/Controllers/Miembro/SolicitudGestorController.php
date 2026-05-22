<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\SolicitudGestor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SolicitudGestorController extends Controller
{
    public function create()
    {
        return view('miembro.form_volverse_gestor');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion_solicitud' => ['nullable', 'string', 'max:1000'],
            'experiencia_solicitud' => ['nullable', 'string', 'max:1000'],
            'acepta_terminos_solicitud' => ['accepted'],
            'acepta_veracidad_solicitud' => ['accepted'],
        ]);

        $usuarioId = Auth::id();

        $tienePendiente = SolicitudGestor::where('id_usuario_fk', $usuarioId)
            ->where('estado_solicitud_gestor', 'pendiente')
            ->exists();

        if ($tienePendiente) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya tienes una solicitud pendiente de revisión.'
                ], 409);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ya tienes una solicitud pendiente de revision.');
        }

        $ahora = Carbon::now();

        SolicitudGestor::create([
            'id_usuario_fk' => $usuarioId,
            'descripcion_solicitud' => $request->input('descripcion_solicitud'),
            'experiencia_solicitud' => $request->input('experiencia_solicitud'),
            'acepta_terminos_solicitud' => true,
            'acepta_veracidad_solicitud' => true,
            'fecha_aceptacion_solicitud' => $ahora,
            'estado_solicitud_gestor' => 'pendiente',
            'creado_solicitud_gestor' => $ahora,
            'actualizado_solicitud_gestor' => $ahora,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada correctamente. Un administrador la revisará pronto.'
            ]);
        }

        return redirect()
            ->route('miembro.gestor.formulario')
            ->with('success', 'Solicitud enviada correctamente. Un administrador la revisara pronto.');
    }
}
