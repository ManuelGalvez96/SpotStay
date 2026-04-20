<?php

namespace Database\Seeders;

use App\Models\ConversacionUsuario;
use App\Models\Conversacion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ConversacionUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();
        $conversaciones = Conversacion::all();
        
        foreach ($conversaciones as $index => $conversacion) {
            $usuarioIndex1 = $index % count($usuarios);
            $usuarioIndex2 = ($index + 1) % count($usuarios);
            
            if ($usuarioIndex1 !== $usuarioIndex2) {
                ConversacionUsuario::firstOrCreate(
                    ['id_conversacion_fk' => $conversacion->id_conversacion, 'id_usuario_fk' => $usuarios[$usuarioIndex1]->id_usuario],
                    [
                        'ultima_lectura_conv_usuario' => now(),
                    ]
                );
                
                ConversacionUsuario::firstOrCreate(
                    ['id_conversacion_fk' => $conversacion->id_conversacion, 'id_usuario_fk' => $usuarios[$usuarioIndex2]->id_usuario],
                    [
                        'ultima_lectura_conv_usuario' => now(),
                    ]
                );
            }
        }
    }
}
