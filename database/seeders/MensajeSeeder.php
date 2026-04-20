<?php

namespace Database\Seeders;

use App\Models\Mensaje;
use App\Models\Conversacion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class MensajeSeeder extends Seeder
{
    public function run(): void
    {
        $mensajes = [
            'Hola, ¿cuándo puedo visitar la propiedad?',
            'El piso está en perfecto estado',
            'Tengo algunas preguntas sobre el contrato',
            '¿Cuáles son los gastos incluidos?',
            'He transferido el depósito de garantía',
            'Necesito información sobre el seguro',
            'La calefacción no funciona',
            '¿Se puede hacer reforma?',
            'Perfecto, todo acordado',
            'Veré el piso el próximo miércoles',
            'Muchas gracias por la información',
            '¿Se admiten mascotas?',
            'Necesito un recibo para la renta',
            'Quisiera cambiar la fecha de pago',
            'El piso superó mis expectativas',
            'Hay un problema con el agua caliente',
            'De acuerdo, procederemos',
            'Espero poder alquilar esta propiedad',
            'Gracias por tu ayuda rápida',
            'Necesito aclarar algunos detalles del contrato',
        ];

        $conversaciones = Conversacion::all();
        $usuarios = Usuario::all();

        if ($conversaciones->isEmpty() || $usuarios->isEmpty()) {
            return;
        }

        $msgIndex = 0;
        foreach ($conversaciones as $conversacion) {
            for ($i = 0; $i < 3; $i++) {
                if ($msgIndex < count($mensajes)) {
                    $usuario = $usuarios->random();

                    Mensaje::firstOrCreate(
                        [
                            'id_conversacion_fk' => $conversacion->id_conversacion,
                            'cuerpo_mensaje' => $mensajes[$msgIndex]
                        ],
                        [
                            'id_remitente_fk' => $usuario->id_usuario,
                            'leido_mensaje' => (bool) rand(0, 1),
                            'creado_mensaje' => now()->subDays(rand(1, 30)),
                            'actualizado_mensaje' => now(),
                        ]
                    );

                    $msgIndex++;
                }
            }
        }
    }
}