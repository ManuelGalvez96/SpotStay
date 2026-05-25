<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\ConversacionUsuario;
use App\Models\Mensaje;
use App\Services\ActividadService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MensajeController extends Controller
{
    use GestorPermisosTrait;

    public function index(Request $request)
    {
        Carbon::setLocale('es');

        $gestorId = (int) Auth::id();

        $conversaciones = Conversacion::with([
            'propiedad',
            'participantes',
            'ultimoMensaje.remitente',
        ])
            ->whereHas('participantes', function ($query) use ($gestorId) {
                $query->where('tbl_conversacion_usuario.id_usuario_fk', $gestorId);
            })
            ->orderByDesc('actualizado_conversacion')
            ->get();

        $conversacionActiva = null;
        $activaId = (int) $request->query('activa', 0);
        if ($activaId > 0) {
            $conversacionActiva = $conversaciones->firstWhere('id_conversacion', $activaId);
        }

        $noLeidos = [];
        foreach ($conversaciones as $conv) {
            $gestorPivot = $conv->participantes->firstWhere('id_usuario', $gestorId);
            $ultimaLectura = $gestorPivot?->pivot?->ultima_lectura_conv_usuario;
            $ultimoMensaje = $conv->ultimoMensaje;
            $noLeidos[$conv->id_conversacion] = $ultimoMensaje && $ultimoMensaje->creado_mensaje &&
                (!$ultimaLectura || $ultimoMensaje->creado_mensaje->gt($ultimaLectura));
        }

        return view('gestor.mensajes', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => $conversacionActiva,
            'gestorId' => $gestorId,
            'noLeidos' => $noLeidos,
        ]);
    }

    public function mostrar(Request $request, int $id): JsonResponse
    {
        $gestorId = (int) Auth::id();

        $conversacion = Conversacion::with('participantes')->find($id);

        if (!$conversacion) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $esParticipante = $conversacion->participantes->contains('id_usuario', $gestorId);
        if (!$esParticipante) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a esta conversación.'], 403);
        }

        ConversacionUsuario::where('id_conversacion_fk', $id)
            ->where('id_usuario_fk', $gestorId)
            ->update(['ultima_lectura_conv_usuario' => Carbon::now()]);

        $otro = $conversacion->participantes->firstWhere('id_usuario', '!=', $gestorId);

        $rol = null;
        if ($conversacion->id_propiedad_fk && $otro) {
            $propArrendadorId = (int) DB::table('tbl_propiedad')
                ->where('id_propiedad', $conversacion->id_propiedad_fk)
                ->value('id_arrendador_fk');
            $rol = ($propArrendadorId === (int) $otro->id_usuario) ? 'Arrendador' : 'Inquilino';
        }

        $mensajes = Mensaje::where('id_conversacion_fk', $id)
            ->orderBy('creado_mensaje', 'asc')
            ->get()
            ->map(function ($m) use ($gestorId) {
                $remitente = $m->remitente;
                $avatarUrl = $remitente ? $remitente->avatar_url : null;
                return [
                    'id_mensaje' => $m->id_mensaje,
                    'id_remitente' => (int) $m->id_remitente_fk,
                    'nombre_remitente' => $remitente->nombre_usuario ?? 'Usuario',
                    'avatar_url' => $avatarUrl,
                    'cuerpo_mensaje' => $m->cuerpo_mensaje,
                    'creado_mensaje' => optional($m->creado_mensaje)->toDateTimeString(),
                    'es_mio' => (int) $m->id_remitente_fk === $gestorId,
                ];
            });

        return response()->json([
            'success' => true,
            'conversacion' => [
                'id_conversacion' => $conversacion->id_conversacion,
                'id_propiedad_fk' => $conversacion->id_propiedad_fk,
                'otro' => $otro ? [
                    'id_usuario' => $otro->id_usuario,
                    'nombre_usuario' => $otro->nombre_usuario,
                    'email_usuario' => $otro->email_usuario,
                    'avatar_url' => $otro ? $otro->avatar_url : null,
                    'rol' => $rol,
                ] : null,
                'mensajes' => $mensajes,
            ],
        ]);
    }

    public function enviar(Request $request, int $id): JsonResponse
    {
        $gestorId = (int) Auth::id();

        $conversacion = Conversacion::with('participantes')->find($id);
        if (!$conversacion) {
            return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
        }

        $esParticipante = $conversacion->participantes->contains('id_usuario', $gestorId);
        if (!$esParticipante) {
            return response()->json(['success' => false, 'message' => 'No tienes acceso a esta conversación.'], 403);
        }

        $datos = $request->validate([
            'texto' => ['required', 'string', 'max:2000'],
        ]);

        $ahora = Carbon::now();

        $mensaje = Mensaje::create([
            'id_conversacion_fk' => $conversacion->id_conversacion,
            'id_remitente_fk' => $gestorId,
            'cuerpo_mensaje' => trim($datos['texto']),
            'leido_mensaje' => false,
            'creado_mensaje' => $ahora,
            'actualizado_mensaje' => $ahora,
        ]);

        $conversacion->update(['actualizado_conversacion' => $ahora]);

        ConversacionUsuario::where('id_conversacion_fk', $conversacion->id_conversacion)
            ->where('id_usuario_fk', $gestorId)
            ->update(['ultima_lectura_conv_usuario' => $ahora]);

        $actividadService = new ActividadService();
        $propiedadTitulo = $conversacion->id_propiedad_fk
            ? (string) DB::table('tbl_propiedad')->where('id_propiedad', $conversacion->id_propiedad_fk)->value('titulo_propiedad')
            : 'tu conversación';
        $extracto = mb_substr(trim($datos['texto']), 0, 120);

            $gestor = Auth::user();
        foreach ($conversacion->participantes as $participante) {
            if ((int) $participante->id_usuario === (int) $gestorId) {
                continue;
            }

            $actividadService->mensajeNuevo(
                (int) $participante->id_usuario,
                (int) $conversacion->id_conversacion,
                $propiedadTitulo,
                $gestor->nombre_usuario ?? 'Un usuario',
                $extracto
            );
        }

        $gestor = Auth::user();

        return response()->json([
            'success' => true,
            'message' => 'Mensaje enviado.',
            'mensaje' => [
                'id_mensaje' => $mensaje->id_mensaje,
                'id_remitente' => $gestorId,
                'nombre_remitente' => $gestor->nombre_usuario,
                'cuerpo_mensaje' => trim($datos['texto']),
                'creado_mensaje' => $ahora->toDateTimeString(),
                'es_mio' => true,
            ],
        ]);
    }

    public function iniciar(Request $request, int $propiedadId)
    {
        $gestorId = (int) Auth::id();

        $permisos = $this->getPermisosPropiedad($gestorId, $propiedadId);
        if (!$permisos->chat) {
            return $this->redirigirSinPermiso('chat');
        }

        $tipo = $request->input('tipo');

        if ($tipo === 'arrendador') {
            $propiedad = DB::table('tbl_propiedad')
                ->where('id_propiedad', $propiedadId)
                ->where('id_gestor_fk', $gestorId)
                ->first(['id_propiedad', 'id_arrendador_fk']);

            if (!$propiedad) {
                abort(404);
            }

            $otroUsuarioId = (int) $propiedad->id_arrendador_fk;

            if ($otroUsuarioId === $gestorId) {
                return redirect()->back()->with('error', 'No puedes iniciar un chat contigo mismo.');
            }
        } elseif ($tipo === 'inquilino') {
            $otroUsuarioId = (int) $request->input('id_usuario');

            $existeAlquiler = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $propiedadId)
                ->where('id_inquilino_fk', $otroUsuarioId)
                ->where('estado_alquiler', 'activo')
                ->exists();

            if (!$existeAlquiler) {
                return redirect()->back()->with('error', 'El inquilino no está activo en esta propiedad.');
            }
        } else {
            return redirect()->back()->with('error', 'Tipo de conversación no válido.');
        }

        $conversacion = Conversacion::where('tipo_conversacion', 'directa')
            ->whereHas('participantes', function ($q) use ($gestorId) {
                $q->where('tbl_conversacion_usuario.id_usuario_fk', $gestorId);
            })
            ->whereHas('participantes', function ($q) use ($otroUsuarioId) {
                $q->where('tbl_conversacion_usuario.id_usuario_fk', $otroUsuarioId);
            })
            ->withCount('participantes')
            ->having('participantes_count', 2)
            ->first();

        if (!$conversacion) {
            $ahora = Carbon::now();

            $conversacion = Conversacion::create([
                'id_propiedad_fk' => $propiedadId,
                'tipo_conversacion' => 'directa',
                'creado_conversacion' => $ahora,
                'actualizado_conversacion' => $ahora,
            ]);

            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $gestorId,
                'ultima_lectura_conv_usuario' => $ahora,
            ]);

            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $otroUsuarioId,
                'ultima_lectura_conv_usuario' => null,
            ]);
        }

        return redirect()->route('gestor.mensajes.index', ['activa' => $conversacion->id_conversacion]);
    }
}
