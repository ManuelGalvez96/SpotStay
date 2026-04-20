<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['nombre' => 'Admin Principal', 'email' => 'admin@spotstay.com', 'rol' => 'admin', 'tel' => '+34 600 000 001'],
            ['nombre' => 'Admin Secundario', 'email' => 'admin2@spotstay.com', 'rol' => 'admin', 'tel' => '+34 600 000 002'],
            // Usuarios usados en PropiedadSeeder
            ['nombre' => 'Arrendador Principal', 'email' => 'arrendador@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 111'],
            ['nombre' => 'Carlos Garcia', 'email' => 'carlos@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 112'],
            ['nombre' => 'Elena Vargas', 'email' => 'elena@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 113'],
            ['nombre' => 'Roberto Mora', 'email' => 'roberto.mora@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 114'],
            ['nombre' => 'Roberto Diaz', 'email' => 'roberto.diaz@email.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 115'],
            ['nombre' => 'Inquilino 1', 'email' => 'inquilino1@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 111'],
            ['nombre' => 'Inquilino 2', 'email' => 'inquilino2@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 112'],
            ['nombre' => 'Inquilino 3', 'email' => 'inquilino3@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 113'],
            ['nombre' => 'Inquilino 4', 'email' => 'inquilino4@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 114'],
            ['nombre' => 'Inquilino 5', 'email' => 'inquilino5@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 115'],
            ['nombre' => 'Inquilino 6', 'email' => 'inquilino6@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 116'],
            ['nombre' => 'Inquilino 7', 'email' => 'inquilino7@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 117'],
            ['nombre' => 'Inquilino 8', 'email' => 'inquilino8@example.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 118'],
            ['nombre' => 'Miguel Gestor', 'email' => 'miguel@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 111'],
            ['nombre' => 'Gestor 2', 'email' => 'gestor2@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 112'],
            ['nombre' => 'Gestor 3', 'email' => 'gestor3@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 113'],
            ['nombre' => 'Usuario Test 1', 'email' => 'test1@example.com', 'rol' => 'inquilino', 'tel' => '+34 604 444 111'],
            ['nombre' => 'Usuario Test 2', 'email' => 'test2@example.com', 'rol' => 'arrendador', 'tel' => '+34 604 444 112'],
        ];

        foreach ($usuarios as $data) {
            $usuario = Usuario::firstOrCreate(
                ['email_usuario' => $data['email']],
                [
                    'nombre_usuario' => $data['nombre'],
                    'contrasena_usuario' => Hash::make('password123'),
                    'telefono_usuario' => $data['tel'],
                    'activo_usuario' => true,
                    'creado_usuario' => now(),
                    'actualizado_usuario' => now(),
                ]
            );

            $rol = Rol::where('slug_rol', $data['rol'])->first();
            if ($rol && !$usuario->roles()->where('id_rol', $rol->id_rol)->exists()) {
                $usuario->roles()->attach($rol->id_rol);
            }
        }
    }
}