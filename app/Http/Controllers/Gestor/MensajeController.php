<?php

namespace App\Http\Controllers\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Conversacion;
use App\Models\ConversacionUsuario;
use App\Models\Mensaje;
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
        $gestorId = (int) Auth::id();

        $propiedadesConChat = $this->getPropiedadesConPermiso($gestorId, 'chat');

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

        return view('gestor.mensajes', [
            'conversaciones' => $conversaciones,
            'conversacionActiva' => $conversacionActiva,
            'gestorId' => $gestorId,
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
                return [
                    'id_mensaje' => $m->id_mensaje,
                    'id_remitente' => (int) $m->id_remitente_fk,
                    'nombre_remitente' => $m->remitente->nombre_usuario ?? 'Usuario',
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
