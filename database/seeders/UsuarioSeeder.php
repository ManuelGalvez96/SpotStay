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

        // Crear usuarios adicionales para alquileres
        $usuariolaraura = Usuario::create([
            'nombre_usuario' => 'Laura Martínez',
            'email_usuario' => 'laura@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 603 333 333',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        if ($rolInquilino) {
            $usuariolaraura->roles()->attach($rolInquilino->id_rol);
        }

        $usuarioPedro = Usuario::create([
            'nombre_usuario' => 'Pedro López',
            'email_usuario' => 'pedro@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 604 444 444',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        if ($rolInquilino) {
            $usuarioPedro->roles()->attach($rolInquilino->id_rol);
        }

        $usuarioSofia = Usuario::create([
            'nombre_usuario' => 'Sofía Rodríguez',
            'email_usuario' => 'sofia@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 605 555 555',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        if ($rolInquilino) {
            $usuarioSofia->roles()->attach($rolInquilino->id_rol);
        }

        $usuarioCarlos = Usuario::create([
            'nombre_usuario' => 'Carlos García',
            'email_usuario' => 'carlos@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 606 666 666',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        if ($rolArrendador) {
            $usuarioCarlos->roles()->attach($rolArrendador->id_rol);
        }

        $usuarioMiguel = Usuario::create([
            'nombre_usuario' => 'Miguel Gestor',
            'email_usuario' => 'miguel@spotstay.com',
            'contrasena_usuario' => Hash::make('password123'),
            'telefono_usuario' => '+34 607 777 777',
            'activo_usuario' => true,
            'creado_usuario' => now(),
            'actualizado_usuario' => now(),
        ]);
        $rolGestor = Rol::where('slug_rol', 'gestor')->first();
        if ($rolGestor) {
            $usuarioMiguel->roles()->attach($rolGestor->id_rol);
        }

        // Crear usuarios adicionales para solicitudes y disponibilidad
        $usuarios_adicionales = [
            ['nombre' => 'Elena Vargas', 'email' => 'elena@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 614 141 414'],
            ['nombre' => 'Roberto Mora', 'email' => 'roberto.mora@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 615 151 515'],
            ['nombre' => 'Ana García', 'email' => 'ana@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 616 161 616'],
            ['nombre' => 'Roberto Díaz', 'email' => 'roberto.diaz@email.com', 'rol' => 'arrendador', 'tel' => '+34 608 888 888'],
            ['nombre' => 'Carmen Iglesias', 'email' => 'carmen.iglesias@email.com', 'rol' => 'arrendador', 'tel' => '+34 609 999 999'],
            ['nombre' => 'Andrés Molina', 'email' => 'andres.molina@email.com', 'rol' => 'inquilino', 'tel' => '+34 610 101 010'],
            ['nombre' => 'Patricia Vega', 'email' => 'patricia.vega@email.com', 'rol' => 'inquilino', 'tel' => '+34 611 111 111'],
            ['nombre' => 'Javier Moya', 'email' => 'javier.moya@email.com', 'rol' => 'inquilino', 'tel' => '+34 612 121 212'],
            ['nombre' => 'Lucía Serrano', 'email' => 'lucia.serrano@email.com', 'rol' => 'inquilino', 'tel' => '+34 613 131 313'],
        ];

        foreach ($usuarios_adicionales as $data) {
            $usuario = Usuario::create([
                'nombre_usuario' => $data['nombre'],
                'email_usuario' => $data['email'],
                'contrasena_usuario' => Hash::make('password123'),
                'telefono_usuario' => $data['tel'],
                'activo_usuario' => true,
                'creado_usuario' => now(),
                'actualizado_usuario' => now(),
            ]);

            $rol = $data['rol'] === 'arrendador' ? $rolArrendador : $rolInquilino;
            if ($rol) {
                $usuario->roles()->attach($rol->id_rol);
            }
        }
    }
}
