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
use Illuminate\Support\Facades\DB;

class MensajesController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::id();
        $conversaciones = $this->obtenerConversacionesUsuario($usuarioId);

        return view('miembro.mensajes', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => null,
            'mensajes' => collect()
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

        return view('miembro.mensajes', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => $conversacionActiva,
            'mensajes' => $conversacionActiva->mensajes
        ]);
    }

    /**
     * Inicia una conversación directa entre el miembro actual y el arrendador de una propiedad.
     * Si el chat ya existe, lo reutiliza; si no, crea la sala y añade a los participantes.
     */
    public function iniciarDesdePropiedad($id)
    {
        $usuarioId = Auth::id();
        $propiedad = Propiedad::select('id_propiedad', 'id_arrendador_fk')
            ->findOrFail($id);

        if ((int) $propiedad->id_arrendador_fk === (int) $usuarioId) {
            return redirect()->back()->with('error', 'No puedes iniciar chat con tu propia propiedad.');
        }

        $conversacion = $this->obtenerOCrearConversacionDirecta(
            (int) $propiedad->id_propiedad,
            (int) $usuarioId,
            (int) $propiedad->id_arrendador_fk
        );

        return redirect()->route('miembro.mensajes.show', ['id' => $conversacion->id_conversacion]);
    }

    /**
     * Inicia una conversación directa entre el miembro actual y el gestor de la propiedad.
     * Solo se permite si el permiso de chat del gestor está activo.
     */
    public function iniciarDesdePropiedadGestor($id)
    {
        $usuarioId = Auth::id();

        $propiedad = Propiedad::select('id_propiedad', 'id_gestor_fk')
            ->findOrFail($id);

        if (empty($propiedad->id_gestor_fk)) {
            return redirect()->back()->with('error', 'Esta propiedad no tiene gestor asignado.');
        }

        if ((int) $propiedad->id_gestor_fk === (int) $usuarioId) {
            return redirect()->back()->with('error', 'No puedes iniciar chat con tu propia cuenta.');
        }

        $permisoChat = DB::table('tbl_propiedad_permisos')
            ->where('id_propiedad_fk', $propiedad->id_propiedad)
            ->where('id_gestor_fk', $propiedad->id_gestor_fk)
            ->value('chat');

        if (!$permisoChat) {
            return redirect()->back()->with('error', 'El gestor no tiene permiso de chat para esta propiedad.');
        }

        $conversacion = $this->obtenerOCrearConversacionDirecta(
            (int) $propiedad->id_propiedad,
            (int) $usuarioId,
            (int) $propiedad->id_gestor_fk
        );

        return redirect()->route('miembro.mensajes.show', ['id' => $conversacion->id_conversacion]);
    }

    /**
     * Busca o crea una conversación directa sobre una propiedad entre dos usuarios.
     */
    private function obtenerOCrearConversacionDirecta(int $idPropiedad, int $usuarioOrigenId, int $usuarioDestinoId)
    {
        $conversacion = Conversacion::where('id_propiedad_fk', $idPropiedad)
            ->where('tipo_conversacion', 'directa')
            ->whereHas('participantes', function ($query) use ($usuarioOrigenId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $usuarioOrigenId);
            })
            ->whereHas('participantes', function ($query) use ($usuarioDestinoId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $usuarioDestinoId);
            })
            ->withCount('participantes')
            ->having('participantes_count', 2)
            ->first();

        if ($conversacion) {
            return $conversacion;
        }

        $ahora = Carbon::now();

        $conversacion = Conversacion::create([
            'id_propiedad_fk' => $idPropiedad,
            'tipo_conversacion' => 'directa',
            'creado_conversacion' => $ahora,
            'actualizado_conversacion' => $ahora,
        ]);

        ConversacionUsuario::create([
            'id_conversacion_fk' => $conversacion->id_conversacion,
            'id_usuario_fk' => $usuarioOrigenId,
            'ultima_lectura_conv_usuario' => $ahora,
        ]);

        ConversacionUsuario::create([
            'id_conversacion_fk' => $conversacion->id_conversacion,
            'id_usuario_fk' => $usuarioDestinoId,
            'ultima_lectura_conv_usuario' => null,
        ]);

        return $conversacion;
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

        $mensaje = Mensaje::create([
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

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'mensaje' => [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'cuerpo_mensaje' => $mensaje->cuerpo_mensaje,
                    'fecha' => optional($mensaje->creado_mensaje)->format('d/m/Y H:i'),
                    'es_mio' => true,
                ],
            ]);
        }

        return redirect()->route('miembro.mensajes.show', ['id' => $conversacion->id_conversacion]);
    }

    public function obtenerMensajes($id)
    {
        $usuarioId = Auth::id();

        $conversacion = Conversacion::with('participantes')
            ->findOrFail($id);

        $esParticipante = $conversacion->participantes
            ->contains('id_usuario', $usuarioId);

        if (!$esParticipante) {
            abort(403);
        }

        $mensajes = Mensaje::where('id_conversacion_fk', $conversacion->id_conversacion)
            ->orderBy('creado_mensaje', 'asc')
            ->get()
            ->map(function ($mensaje) use ($usuarioId) {
                return [
                    'id_mensaje' => $mensaje->id_mensaje,
                    'cuerpo_mensaje' => $mensaje->cuerpo_mensaje,
                    'fecha' => optional($mensaje->creado_mensaje)->format('d/m/Y H:i'),
                    'es_mio' => (int) $mensaje->id_remitente_fk === (int) $usuarioId,
                ];
            })
            ->values();

        return response()->json([
            'ok' => true,
            'mensajes' => $mensajes,
        ]);
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
