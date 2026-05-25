<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSesion;
use App\Models\ChatbotMensaje;
use App\Models\ArticuloAsesoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
Eres Spoty, el asistente legal de SpotStay. Eres un asistente legal especializado en derecho español, con enfoque en alquiler, vivienda, contratos de arrendamiento y normativa relacionada. Respondes en español de forma clara, amable y cercana, como un amigo que entiende de leyes. Usa un tono cálido y accesible. Si no sabes algo, lo dices honestamente. No eres un sustituto de un abogado profesional.
PROMPT;

    public function iniciarSesion()
    {
        $usuarioId = auth()->id();

        $sesion = ChatbotSesion::create([
            'id_usuario_fk' => $usuarioId,
            'creado_sesion_chatbot' => now(),
            'actualizado_sesion_chatbot' => now(),
        ]);

        return response()->json([
            'id_sesion_chatbot' => $sesion->id_sesion_chatbot,
        ]);
    }

    public function enviarMensaje(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:2000',
            'id_sesion_chatbot' => 'required|integer|exists:tbl_chatbot_sesion,id_sesion_chatbot',
        ]);

        $usuarioId = auth()->id();
        $sesionId = $request->id_sesion_chatbot;
        $mensajeUsuario = trim($request->mensaje);

        // Verificar que la sesión pertenece al usuario
        $sesion = ChatbotSesion::where('id_sesion_chatbot', $sesionId)
            ->where('id_usuario_fk', $usuarioId)
            ->firstOrFail();

        // Guardar mensaje del usuario
        ChatbotMensaje::create([
            'id_sesion_chatbot_fk' => $sesionId,
            'rol_mensaje_chatbot' => 'usuario',
            'cuerpo_mensaje_chatbot' => $mensajeUsuario,
            'creado_mensaje_chatbot' => now(),
        ]);

        // Obtener historial de la sesión
        $historial = ChatbotMensaje::where('id_sesion_chatbot_fk', $sesionId)
            ->orderBy('creado_mensaje_chatbot', 'asc')
            ->get();

        // Buscar artículos relevantes como contexto (RAG opcional)
        $contextoArticulos = $this->buscarArticulosRelevantes($mensajeUsuario);

        // Construir mensajes en formato OpenAI (Groq)
        $mensajes = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt($contextoArticulos)],
        ];
        foreach ($historial as $msg) {
            $role = $msg->rol_mensaje_chatbot === 'usuario' ? 'user' : 'assistant';
            $mensajes[] = ['role' => $role, 'content' => $msg->cuerpo_mensaje_chatbot];
        }

        try {
            $httpRequest = Http::timeout(30);

            if (app()->environment('local')) {
                $httpRequest = $httpRequest->withoutVerifying();
            }

            $response = $httpRequest->withToken(config('services.groq.api_key'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $mensajes,
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                ]);

            if ($response->failed()) {
                throw new \Exception('Groq respondió con estado ' . $response->status() . ': ' . $response->body());
            }

            $data = $response->json();
            $respuestaTexto = $data['choices'][0]['message']['content'] ?? 'No he podido procesar tu consulta. Intentalo de nuevo.';
        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $respuestaTexto = 'Lo siento, ahora mismo no puedo responder. Intentalo de nuevo en un momento.';
        }

        // Guardar respuesta de la IA
        $mensajeAi = ChatbotMensaje::create([
            'id_sesion_chatbot_fk' => $sesionId,
            'rol_mensaje_chatbot' => 'chatbot',
            'cuerpo_mensaje_chatbot' => $respuestaTexto,
            'creado_mensaje_chatbot' => now(),
        ]);

        // Actualizar timestamp de la sesión
        $sesion->update(['actualizado_sesion_chatbot' => now()]);

        return response()->json([
            'respuesta' => $respuestaTexto,
            'id_mensaje' => $mensajeAi->id_mensaje_chatbot,
        ]);
    }

    public function historial(Request $request)
    {
        $request->validate([
            'id_sesion_chatbot' => 'required|integer|exists:tbl_chatbot_sesion,id_sesion_chatbot',
        ]);

        $usuarioId = auth()->id();
        $sesionId = $request->id_sesion_chatbot;

        $sesion = ChatbotSesion::where('id_sesion_chatbot', $sesionId)
            ->where('id_usuario_fk', $usuarioId)
            ->firstOrFail();

        $mensajes = ChatbotMensaje::where('id_sesion_chatbot_fk', $sesionId)
            ->orderBy('creado_mensaje_chatbot', 'asc')
            ->get(['rol_mensaje_chatbot', 'cuerpo_mensaje_chatbot', 'creado_mensaje_chatbot']);

        return response()->json($mensajes);
    }

    private function buildSystemPrompt(string $contextoArticulos = ''): string
    {
        $prompt = self::SYSTEM_PROMPT;

        if ($contextoArticulos) {
            $prompt .= "\n\nAquí tienes información de nuestros artículos de asesoría que puede ser relevante para la consulta:\n\n" . $contextoArticulos;
        }

        return $prompt;
    }

    private function buscarArticulosRelevantes(string $consulta): string
    {
        $palabras = array_filter(explode(' ', mb_strtolower($consulta)), fn($p) => mb_strlen($p) > 3);

        if (empty($palabras)) {
            return '';
        }

        $articulos = ArticuloAsesoria::with('categoria')
            ->where('estado', 1)
            ->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->orWhere('titulo', 'like', '%' . $palabra . '%')
                      ->orWhere('contenido', 'like', '%' . $palabra . '%');
                }
            })
            ->limit(3)
            ->get();

        if ($articulos->isEmpty()) {
            return '';
        }

        $contexto = '';
        foreach ($articulos as $art) {
            $contexto .= "- {$art->titulo}";
            if ($art->categoria) {
                $contexto .= " (categoría: {$art->categoria->nombre})";
            }
            $contexto .= "\n";
            $texto = strip_tags($art->contenido);
            $contexto .= mb_substr($texto, 0, 500) . "\n\n";
        }

        return trim($contexto);
    }

    private function routePrefix(): string
    {
        if (request()->routeIs('gestor.*')) return 'gestor';
        if (request()->routeIs('arrendador.*')) return 'arrendador';
        if (request()->routeIs('inquilino.*')) return 'inquilino';
        return 'miembro';
    }
}
