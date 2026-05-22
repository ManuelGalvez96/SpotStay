<?php

namespace App\Http\Controllers\Miembro;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\ConversacionUsuario;
use App\Models\Mensaje;
use App\Models\Propiedad;
use App\Services\ActividadService;
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
        // 1. IDENTIFICACIÓN: Obtenemos quién intenta iniciar el chat
        $usuarioId = Auth::id();

        // Obtenemos la propiedad específica y quién es su dueño (arrendador_fk)
        $propiedad = Propiedad::select('id_propiedad', 'id_arrendador_fk')
            ->findOrFail($id);

        // 2. SEGURIDAD: Evitar que el dueño chatee consigo mismo
        if ((int) $propiedad->id_arrendador_fk === (int) $usuarioId) {
            return redirect()->back()->with('error', 'No puedes iniciar chat con tu propia propiedad.');
        }

        // 3. BÚSQUEDA: ¿Existe ya un chat entre ESTOS dos sobre ESTA propiedad?
        // Usamos una cadena de condiciones para ser muy estrictos:
        $conversacion = Conversacion::where('id_propiedad_fk', $propiedad->id_propiedad)
            ->where('tipo_conversacion', 'directa')
            
            // FILTRO A: La conversación debe incluir al USUARIO ACTUAL
            ->whereHas('participantes', function ($query) use ($usuarioId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $usuarioId);
            })
            
            // FILTRO B: La conversación debe incluir al DUEÑO de la propiedad
            ->whereHas('participantes', function ($query) use ($propiedad) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $propiedad->id_arrendador_fk);
            })
            
            // FILTRO C: Deben haber EXACTAMENTE 2 personas (ni más, ni menos)
            ->withCount('participantes')
            ->having('participantes_count', 2)
            
            // Traemos el resultado (o null si no encuentra nada)
            ->first();

        // 4. CREACIÓN: Si no existe la conversación, la creamos desde cero
        if (!$conversacion) {
            $ahora = Carbon::now();

            // A. Crear la "sala" principal (tbl_conversacion)
            $conversacion = Conversacion::create([
                'id_propiedad_fk' => $propiedad->id_propiedad,
                'tipo_conversacion' => 'directa',
                'creado_conversacion' => $ahora,
                'actualizado_conversacion' => $ahora,
            ]);

            // B. Registrar al USUARIO ACTUAL como participante (tbl_conversacion_usuario)
            // Ponemos 'ultima_lectura' en 'ahora' porque él acaba de entrar
            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $usuarioId,
                'ultima_lectura_conv_usuario' => $ahora,
            ]);

            // C. Registrar al ARRENDADOR como participante (tbl_conversacion_usuario)
            // Ponemos 'ultima_lectura' en 'null' porque el dueño aún no ha visto nada
            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $propiedad->id_arrendador_fk,
                'ultima_lectura_conv_usuario' => null,
            ]);
        }

        // 5. REDIRECCIÓN: Llevamos al usuario a la vista del chat activo
        return redirect()->route('miembro.mensajes.show', ['id' => $conversacion->id_conversacion]);
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

        $actividadService = new ActividadService();
        $usuario = Auth::user();
        $propiedadTitulo = $conversacion->propiedad->titulo_propiedad ?? 'tu conversación';
        $extracto = mb_substr(trim((string) $request->input('mensaje')), 0, 120);

        foreach ($conversacion->participantes as $participante) {
            if ((int) $participante->id_usuario === (int) $usuarioId) {
                continue;
            }

            $actividadService->mensajeNuevo(
                (int) $participante->id_usuario,
                (int) $conversacion->id_conversacion,
                $propiedadTitulo,
                $usuario->nombre_usuario ?? 'Un usuario',
                $extracto
            );
        }

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
