<?php

namespace Database\Seeders;

use App\Models\ChatbotMensaje;
use App\Models\ChatbotSesion;
use Illuminate\Database\Seeder;

class ChatbotMensajeSeeder extends Seeder
{
    public function run(): void
    {
        $mensajes = [
            'Hola, ¿cómo puedo ayudarte?',
            'Qué información necesitas?',
            'Cuéntame más detalles.',
            'Entiendo tu problema.',
            'Aquí está la información que solicitaste.',
            'Gracias por tu pregunta.',
            'Puedo ayudarte con eso.',
            'Por favor espera mientras verifico.',
            'La respuesta a tu pregunta es...',
            'Necesitaré más información.',
            'De acuerdo, lo anotaré.',
            'Alguien te contactará pronto.',
            'Esta es una pregunta frecuente.',
            'Consulta nuestra documentación.',
            'Lamento no poder ayudarte con eso.',
            'Para más información, contacta soporte.',
            'Perfecto, creo que hemos resuelto tu duda.',
            'Hay otras cosas en las que pueda ayudarte?',
            'Gracias por usar nuestro servicio.',
            'Hasta luego, que tengas un buen día!',
        ];

        $sesiones = ChatbotSesion::all();
        $mensajeIndex = 0;
        $roles = ['usuario', 'chatbot'];
        $rolIndex = 0;

        foreach ($sesiones as $sesion) {
            for ($i = 0; $i < 5; $i++) {
                $mensaje = $mensajes[$mensajeIndex % count($mensajes)];
                $rol = $roles[$rolIndex % count($roles)];

                ChatbotMensaje::firstOrCreate(
                    ['id_sesion_chatbot_fk' => $sesion->id_sesion_chatbot, 'cuerpo_mensaje_chatbot' => $mensaje],
                    [
                        'rol_mensaje_chatbot' => $rol,
                        'creado_mensaje_chatbot' => now(),
                    ]
                );

                $mensajeIndex++;
                $rolIndex++;
            }
        }
    }
}
