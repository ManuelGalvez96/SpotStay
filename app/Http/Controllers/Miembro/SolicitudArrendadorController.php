<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\SolicitudArrendador;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudArrendadorController extends Controller
{
    public function create()
    {
        return view('miembro.form_volverse_arrendador');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_arrendador_solicitud' => ['required', 'string', 'in:particular,empresa'],
            'descripcion_solicitud' => ['nullable', 'string'],
            'num_propiedades_previstas_solicitud' => ['nullable', 'integer', 'min:1', 'max:255'],
            'es_propietario_solicitud' => ['nullable', 'boolean'],
            'acepta_terminos_solicitud' => ['accepted'],
            'acepta_veracidad_solicitud' => ['accepted'],
        ]);

        $usuario = Auth::user();
        $usuarioId = $usuario->id_usuario;

        $tienePendiente = SolicitudArrendador::where('id_usuario_fk', $usuarioId)
            ->where('estado_solicitud_arrendador', 'pendiente')
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

        SolicitudArrendador::create([
            'id_usuario_fk' => $usuarioId,
            'telefono_solicitud' => $usuario->telefono_usuario,
            'fecha_nacimiento_solicitud' => $usuario->fecha_nacimiento_usuario,
            'tipo_documento_solicitud' => null,
            'numero_documento_solicitud' => $usuario->dni_usuario,
            'iban_solicitud' => null,
            'titular_cuenta_solicitud' => null,
            'nif_solicitud' => null,
            'direccion_fiscal_solicitud' => null,
            'tipo_arrendador_solicitud' => $request->input('tipo_arrendador_solicitud'),
            'descripcion_solicitud' => $request->input('descripcion_solicitud'),
            'num_propiedades_previstas_solicitud' => $request->input('num_propiedades_previstas_solicitud'),
            'es_propietario_solicitud' => $request->boolean('es_propietario_solicitud'),
            'acepta_terminos_solicitud' => true,
            'acepta_veracidad_solicitud' => true,
            'fecha_aceptacion_solicitud' => $ahora,
            'estado_solicitud_arrendador' => 'pendiente',
            'creado_solicitud_arrendador' => $ahora,
            'actualizado_solicitud_arrendador' => $ahora,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada correctamente. Un administrador la revisará pronto.'
            ]);
        }

        return redirect()
            ->route('miembro.arrendador.formulario')
            ->with('success', 'Solicitud enviada correctamente. Un administrador la revisara pronto.');
    }
}
