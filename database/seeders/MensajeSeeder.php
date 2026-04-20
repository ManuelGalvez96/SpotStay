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
        // Conversación Calle Mayor 14 (Carlos García y Laura Martínez)
        $conversacionCalle = DB::table('tbl_conversacion')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_conversacion.id_propiedad_fk')
            ->whereRaw("TRIM(CONCAT_WS(' ', tbl_propiedad.calle_propiedad, tbl_propiedad.numero_propiedad)) = ?", ['Calle Mayor 14'])
            ->where('tbl_conversacion.tipo_conversacion', 'directa')
            ->value('id_conversacion');

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