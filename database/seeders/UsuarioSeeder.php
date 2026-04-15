<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Inserta un usuario admin de prueba
     */
    public function run(): void
    {
        // Crear usuario admin
        $usuarioAdmin = Usuario::create([
            'nombre_usuario' => 'Administrador',
            'email_usuario' => 'admin@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 600 000 000',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);

        // Asignar rol admin al usuario
        $rolAdmin = Rol::where('slug_rol', 'admin')->first();
        if ($rolAdmin) {
            $usuarioAdmin->roles()->attach($rolAdmin->id_rol);
        }

        // Crear usuario de prueba arrendador
        $usuarioArrendador = Usuario::create([
            'nombre_usuario' => 'Juan Pérez',
            'email_usuario' => 'arrendador@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 601 111 111',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);

        $rolArrendador = Rol::where('slug_rol', 'arrendador')->first();
        if ($rolArrendador) {
            $usuarioArrendador->roles()->attach($rolArrendador->id_rol);
        }

        // Crear usuario de prueba inquilino
        $usuarioInquilino = Usuario::create([
            'nombre_usuario' => 'María García',
            'email_usuario' => 'inquilino@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 602 222 222',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);

        $rolInquilino = Rol::where('slug_rol', 'inquilino')->first();
        if ($rolInquilino) {
            $usuarioInquilino->roles()->attach($rolInquilino->id_rol);
        }
    }
}
