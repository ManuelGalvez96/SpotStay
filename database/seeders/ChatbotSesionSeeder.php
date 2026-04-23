<?php

namespace Database\Seeders;

use App\Models\ChatbotSesion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class ChatbotSesionSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::all();
        
        foreach ($usuarios as $usuario) {
            // Create 1-2 chatbot sessions per user
            for ($i = 0; $i < rand(1, 2); $i++) {
                ChatbotSesion::create([
                    'id_usuario_fk' => $usuario->id_usuario,
                    'creado_sesion_chatbot' => now()->subDays(rand(1, 30)),
                    'actualizado_sesion_chatbot' => now(),
                ]);
            }
        }
    }
}
