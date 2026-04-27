<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\ConversacionUsuario;
use App\Models\Mensaje;
use App\Models\Propiedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ChatController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::id();

        $conversaciones = $this->obtenerConversacionesUsuario($usuarioId);

        return view('miembro.chat', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => null,
            'mensajes' => collect(),
        ]);
    }

    public function show($id)
    {
        $usuarioId = Auth::id();

        $conversacionActiva = Conversacion::with([
            'propiedad',
            'participantes',
            'mensajes.remitente',
        ])->findOrFail($id);

        $esParticipante = $conversacionActiva->participantes
            ->contains('id_usuario', $usuarioId);

        if (!$esParticipante) {
            abort(403);
        }

        $conversaciones = $this->obtenerConversacionesUsuario($usuarioId);

        return view('miembro.chat', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => $conversacionActiva,
            'mensajes' => $conversacionActiva->mensajes,
        ]);
    }

    public function iniciarDesdePropiedad($id)
    {
        $usuarioId = Auth::id();

        $propiedad = Propiedad::select('id_propiedad', 'id_arrendador_fk')
            ->findOrFail($id);

        if ((int) $propiedad->id_arrendador_fk === (int) $usuarioId) {
            return redirect()->back()->with('error', 'No puedes iniciar chat con tu propia propiedad.');
        }

        $conversacion = Conversacion::where('id_propiedad_fk', $propiedad->id_propiedad)
            ->where('tipo_conversacion', 'directa')
            ->whereHas('participantes', function ($query) use ($usuarioId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $usuarioId);
            })
            ->whereHas('participantes', function ($query) use ($propiedad) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $propiedad->id_arrendador_fk);
            })
            ->withCount('participantes')
            ->having('participantes_count', 2)
            ->first();

        if (!$conversacion) {
            $ahora = Carbon::now();

            $conversacion = Conversacion::create([
                'id_propiedad_fk' => $propiedad->id_propiedad,
                'tipo_conversacion' => 'directa',
                'creado_conversacion' => $ahora,
                'actualizado_conversacion' => $ahora,
            ]);

            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $usuarioId,
                'ultima_lectura_conv_usuario' => $ahora,
            ]);

            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $propiedad->id_arrendador_fk,
                'ultima_lectura_conv_usuario' => null,
            ]);
        }

        return redirect()->route('miembro.chat.show', ['id' => $conversacion->id_conversacion]);
    }

    public function enviarMensaje(Request $request, $id)
    {
        $request->validate([
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        $usuarioId = Auth::id();

        $conversacion = Conversacion::with('participantes')
            ->findOrFail($id);

        $esParticipante = $conversacion->participantes
            ->contains('id_usuario', $usuarioId);

        if (!$esParticipante) {
            abort(403);
        }

        $ahora = Carbon::now();

        Mensaje::create([
            'id_conversacion_fk' => $conversacion->id_conversacion,
            'id_remitente_fk' => $usuarioId,
            'cuerpo_mensaje' => $request->input('mensaje'),
            'leido_mensaje' => false,
            'creado_mensaje' => $ahora,
            'actualizado_mensaje' => $ahora,
        ]);

        $conversacion->update([
            'actualizado_conversacion' => $ahora,
        ]);

        ConversacionUsuario::where('id_conversacion_fk', $conversacion->id_conversacion)
            ->where('id_usuario_fk', $usuarioId)
            ->update([
                'ultima_lectura_conv_usuario' => $ahora,
            ]);

        return redirect()->route('miembro.chat.show', ['id' => $conversacion->id_conversacion]);
    }

    private function obtenerConversacionesUsuario($usuarioId)
    {
        return Conversacion::with([
            'propiedad',
            'participantes',
            'ultimoMensaje.remitente',
        ])
            ->whereHas('participantes', function ($query) use ($usuarioId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $usuarioId);
            })
            ->orderByDesc('actualizado_conversacion')
            ->get();
    }
}
