<?php

namespace Database\Seeders;

use App\Models\Conversacion;
use App\Models\ConversacionUsuario;
use App\Models\Mensaje;
use App\Models\Propiedad;
use App\Models\Alquiler;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConversacionSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = Propiedad::all();
        $alquileres = Alquiler::all();
        $usuarios = Usuario::all();

        if ($propiedades->isEmpty() || $usuarios->isEmpty()) {
            return;
        }

        $conversacionesData = [];
        $conversacionCounter = 0;

        // Conversaciones arrendador-inquilino sobre propiedades
        foreach ($alquileres->take(15) as $alquiler) {
            $propiedad = $alquiler->propiedad;
            $inquilino = Usuario::find($alquiler->id_inquilino_fk);
            $arrendador = $propiedad->arrendador;

            if (!$inquilino || !$arrendador) {
                continue;
            }

            $conversacion = Conversacion::firstOrCreate(
                [
                    'id_propiedad_fk' => $propiedad->id_propiedad,
                    'tipo_conversacion' => 'consulta_propiedad',
                ],
                [
                    'creado_conversacion' => now()->subDays(rand(5, 60)),
                    'actualizado_conversacion' => now(),
                ]
            );

            // Agregar participantes
            ConversacionUsuario::firstOrCreate(
                [
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_usuario_fk' => $inquilino->id_usuario,
                ],
                ['ultima_lectura_conv_usuario' => now()]
            );

            ConversacionUsuario::firstOrCreate(
                [
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_usuario_fk' => $arrendador->id_usuario,
                ],
                ['ultima_lectura_conv_usuario' => now()]
            );

            // Crear mensajes
            $mensajesContenido = [
                '¿Cuándo puedo venir a ver la propiedad?',
                'La propiedad parece muy interesante, ¿está disponible este mes?',
                'Me gustaría conocer más sobre los servicios incluidos.',
                '¿Cuál es el proceso de arrendamiento?',
                'Estoy muy interesado, ¿cuáles son los requisitos?',
            ];

            $mensajeInicial = $conversacion->mensajes()->first();
            if (!$mensajeInicial) {
                Mensaje::create([
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_remitente_fk' => $inquilino->id_usuario,
                    'cuerpo_mensaje' => $mensajesContenido[rand(0, count($mensajesContenido) - 1)],
                    'leido_mensaje' => true,
                    'creado_mensaje' => $conversacion->creado_conversacion,
                    'actualizado_mensaje' => now(),
                ]);
            }

            $conversacionCounter++;
        }

        // Conversaciones sobre incidencias
        $incidencias = \App\Models\Incidencia::all();
        
        foreach ($incidencias->take(15) as $incidencia) {
            $propiedad = $incidencia->propiedad;
            $reportador = $incidencia->reportadaPor;
            $asignado = $incidencia->asignadaA;

            if (!$propiedad || !$reportador || !$asignado) {
                continue;
            }

            $conversacion = Conversacion::firstOrCreate(
                [
                    'id_propiedad_fk' => $propiedad->id_propiedad,
                    'tipo_conversacion' => 'incidencia_reporte',
                ],
                [
                    'creado_conversacion' => $incidencia->creado_incidencia,
                    'actualizado_conversacion' => now(),
                ]
            );

            // Agregar participantes
            ConversacionUsuario::firstOrCreate(
                [
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_usuario_fk' => $reportador->id_usuario,
                ],
                ['ultima_lectura_conv_usuario' => now()]
            );

            ConversacionUsuario::firstOrCreate(
                [
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_usuario_fk' => $asignado->id_usuario,
                ],
                ['ultima_lectura_conv_usuario' => now()]
            );

            // Crear mensajes sobre la incidencia
            $mensajesIncidencia = [
                'He reportado un problema grave: ' . $incidencia->titulo_incidencia,
                'Por favor, revisar la incidencia reportada urgentemente.',
                '¿Cuándo pueden venir a arreglarlo?',
                'El problema sigue sin resolverse, ¿alguna actualización?',
                'Gracias por el pronto arreglo!',
            ];

            $mensajeInicial = $conversacion->mensajes()->first();
            if (!$mensajeInicial) {
                Mensaje::create([
                    'id_conversacion_fk' => $conversacion->id_conversacion,
                    'id_remitente_fk' => $reportador->id_usuario,
                    'cuerpo_mensaje' => $mensajesIncidencia[0],
                    'leido_mensaje' => true,
                    'creado_mensaje' => $incidencia->creado_incidencia,
                    'actualizado_mensaje' => now(),
                ]);

                // Respuesta del gestor
                if (rand(0, 1) === 0) {
                    Mensaje::create([
                        'id_conversacion_fk' => $conversacion->id_conversacion,
                        'id_remitente_fk' => $asignado->id_usuario,
                        'cuerpo_mensaje' => 'Hemos recibido el reporte. ' . $mensajesIncidencia[1],
                        'leido_mensaje' => true,
                        'creado_mensaje' => $incidencia->creado_incidencia->addHours(rand(1, 24)),
                        'actualizado_mensaje' => now(),
                    ]);
                }
            }

            $conversacionCounter++;
        }

        // Conversaciones generales entre usuarios (arrendadores e inquilinos)
        $tiposConversacion = ['consulta_general', 'documentacion', 'pago'];
        
        for ($i = 0; $i < 3; $i++) {
            $usuario1 = $usuarios->random();
            $usuario2 = $usuarios->random();

            if ($usuario1->id_usuario === $usuario2->id_usuario) {
                continue;
            }

            $conversacion = Conversacion::create([
                'id_propiedad_fk' => null,
                'tipo_conversacion' => $tiposConversacion[rand(0, count($tiposConversacion) - 1)],
                'creado_conversacion' => now()->subDays(rand(5, 30)),
                'actualizado_conversacion' => now(),
            ]);

            // Participantes
            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $usuario1->id_usuario,
                'ultima_lectura_conv_usuario' => now(),
            ]);

            ConversacionUsuario::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_usuario_fk' => $usuario2->id_usuario,
                'ultima_lectura_conv_usuario' => now(),
            ]);

            // Mensajes
            $mensajesGenerales = [
                '¿Podrías ayudarme con este trámite?',
                'Tengo una consulta sobre mi arrendamiento.',
                'Necesito información sobre los pagos.',
                '¿Puedo realizar el pago de otra forma?',
                'Gracias por tu ayuda, fue muy útil.',
                'Espero podamos resolver esto pronto.',
                '¿Cuándo tienes disponibilidad?',
                'He enviado la documentación requerida.',
            ];

            Mensaje::create([
                'id_conversacion_fk' => $conversacion->id_conversacion,
                'id_remitente_fk' => $usuario1->id_usuario,
                'cuerpo_mensaje' => $mensajesGenerales[rand(0, count($mensajesGenerales) - 1)],
                'leido_mensaje' => rand(0, 1) === 0,
                'creado_mensaje' => $conversacion->creado_conversacion,
                'actualizado_mensaje' => now(),
            ]);

            $conversacionCounter++;
        }
    }
}