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
            'Hola, tengo una pregunta sobre mi contrato.',
            'Necesito información sobre los términos de pago.',
            'Por favor, confirma la fecha de inspección.',
            'El problema de humedad sigue presente.',
            'Gracias por tu rápida respuesta.',
            'Necesito una extensión del período de renta.',
            'Cuáles son los cargos adicionales?',
            'La propiedad está en buenas condiciones.',
            'Me gustaría renovar mi contrato por otro año.',
            'El grifista ya visited la propiedad.',
            'Tengo que reportar un nuevo problema.',
            'Los documentos están listos para firmar.',
            'Solicito una inspección de la propiedad.',
            'El pago se realizó correctamente.',
            'Necesito clarificación sobre el depósito.',
            'Las reparaciones fueron completadas ayer.',
            'Tengo dudas sobre el proceso de cancelación.',
            'Quiero cambiar de propiedad en el próximo mes.',
            'El contrato ha sido firmado digitalmente.',
            'Requiero asistencia técnica inmediata.',
        ];

        $conversaciones = Conversacion::all();
        $usuarios = Usuario::all();
        
        $mensajeIndex = 0;
        foreach ($conversaciones as $conversacion) {
            for ($i = 0; $i < 3; $i++) {
                if ($mensajeIndex < count($mensajes) && !$usuarios->isEmpty()) {
                    $usuario = $usuarios->random();
                    
                    Mensaje::firstOrCreate(
                        ['id_conversacion_fk' => $conversacion->id_conversacion, 'cuerpo_mensaje' => $mensajes[$mensajeIndex]],
                        [
                            'id_remitente_fk' => $usuario->id_usuario,
                            'leido_mensaje' => (bool) rand(0, 1),
                            'creado_mensaje' => now()->subDays(rand(1, 10)),
                            'actualizado_mensaje' => now(),
                        ]
                    );
                    $mensajeIndex++;
                }
            }
        }
    }
}