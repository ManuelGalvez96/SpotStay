<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MensajeSeeder extends Seeder
{
    public function run(): void
    {
        // Conversación Calle Mayor 14 (Carlos García y Laura Martínez)
        $conversacionCalle = DB::table('tbl_conversacion')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_conversacion.id_propiedad_fk')
            ->where('tbl_propiedad.direccion_propiedad', 'Calle Mayor 14')
            ->where('tbl_conversacion.tipo_conversacion', 'directa')
            ->value('id_conversacion');

        if ($conversacionCalle) {
            $carlosId = DB::table('tbl_usuario')->where('email_usuario', 'carlos@spotstay.com')->value('id_usuario');
            $lauraId = DB::table('tbl_usuario')->where('email_usuario', 'laura@spotstay.com')->value('id_usuario');

            $mensajesCalle = [
                ['id_usuario_fk' => $lauraId, 'contenido' => 'Buenos días Carlos, el grifo del baño sigue goteando', 'leido' => true],
                ['id_usuario_fk' => $carlosId, 'contenido' => 'Hola Laura, lo revisaré esta semana sin falta', 'leido' => true],
                ['id_usuario_fk' => $lauraId, 'contenido' => 'Muchas gracias, también hay algo de humedad en el baño', 'leido' => true],
                ['id_usuario_fk' => $carlosId, 'contenido' => 'Anotado, llamaré a un fontanero para los dos problemas', 'leido' => true],
                ['id_usuario_fk' => $lauraId, 'contenido' => 'Perfecto, muchas gracias por la rapidez', 'leido' => false],
            ];

            foreach ($mensajesCalle as $index => $msg) {
                DB::table('tbl_mensaje')->insert([
                    'id_conversacion_fk' => $conversacionCalle,
                    'id_usuario_fk' => $msg['id_usuario_fk'],
                    'contenido_mensaje' => $msg['contenido'],
                    'leido_mensaje' => $msg['leido'],
                    'creado_mensaje' => Carbon::now()->addHours($index),
                ]);
            }
        }

        // Resto de conversaciones: 3-4 mensajes genéricos
        $conversaciones = DB::table('tbl_conversacion')
            ->where('id_conversacion', '!=', $conversacionCalle ?? 0)
            ->get();

        foreach ($conversaciones as $conv) {
            $participantes = DB::table('tbl_conversacion_usuario')
                ->where('id_conversacion_fk', $conv->id_conversacion)
                ->pluck('id_usuario_fk')
                ->toArray();

            // 3-4 mensajes alternando participantes
            $numMensajes = rand(3, 4);
            $mensajesGenericos = [
                'Hola, ¿cómo estás?',
                'Necesito hablar contigo sobre algunos detalles',
                'Perfecto, de acuerdo.',
                'Muchas gracias por tu ayuda',
                '¿Podemos coordinar una reunión esta semana?',
                'Claro, sin problema',
                'Te contactaré mañana',
                'De acuerdo, esperamos noticias tuyas',
            ];

            $emailsParticipantes = DB::table('tbl_usuario')
                ->whereIn('id_usuario', $participantes)
                ->pluck('email_usuario')
                ->toArray();

            for ($i = 0; $i < $numMensajes; $i++) {
                $idRemitente = $participantes[$i % count($participantes)];
                $contenido = $mensajesGenericos[rand(0, count($mensajesGenericos) - 1)];
                $leido = ($i < $numMensajes - 1); // El último no está leído

                DB::table('tbl_mensaje')->insert([
                    'id_conversacion_fk' => $conv->id_conversacion,
                    'id_usuario_fk' => $idRemitente,
                    'contenido_mensaje' => $contenido,
                    'leido_mensaje' => $leido,
                    'creado_mensaje' => Carbon::now()->addHours($i),
                ]);
            }
        }
    }
}
