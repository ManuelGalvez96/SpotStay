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
            // Todas las contraseñas son "password123"
            // ADMINS (3 máximo)
            ['nombre' => 'Admin Principal', 'apellido' => 'García', 'email' => 'agarcia@spotstay.com', 'rol' => 'admin', 'tel' => '+34 600 000 001'],
            ['nombre' => 'Admin Secundario', 'apellido' => 'López', 'email' => 'alopez@spotstay.com', 'rol' => 'admin', 'tel' => '+34 600 000 002'],
            ['nombre' => 'Admin Support', 'apellido' => 'Martínez', 'email' => 'amartinez@spotstay.com', 'rol' => 'admin', 'tel' => '+34 600 000 003'],

            // GESTORES
            ['nombre' => 'Miguel', 'apellido' => 'Gestor', 'email' => 'mgestor@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 111'],
            ['nombre' => 'Ana', 'apellido' => 'Fernández', 'email' => 'afernandez@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 112'],
            ['nombre' => 'Carlos', 'apellido' => 'Romero', 'email' => 'cromero@spotstay.com', 'rol' => 'gestor', 'tel' => '+34 603 333 113'],

            // MIEMBROS (5 máximo)
            ['nombre' => 'Roberto', 'apellido' => 'Díaz', 'email' => 'rdiaz@spotstay.com', 'rol' => 'miembro', 'tel' => '+34 604 111 111'],
            ['nombre' => 'Carmen', 'apellido' => 'Sánchez', 'email' => 'csanchez@spotstay.com', 'rol' => 'miembro', 'tel' => '+34 604 111 112'],
            ['nombre' => 'Francisco', 'apellido' => 'Pérez', 'email' => 'fperez@spotstay.com', 'rol' => 'miembro', 'tel' => '+34 604 111 113'],
            ['nombre' => 'Beatriz', 'apellido' => 'González', 'email' => 'bgonzalez@spotstay.com', 'rol' => 'miembro', 'tel' => '+34 604 111 114'],
            ['nombre' => 'Diego', 'apellido' => 'Rodríguez', 'email' => 'drodriguez@spotstay.com', 'rol' => 'miembro', 'tel' => '+34 604 111 115'],

            // ARRENDADORES (25+)
            ['nombre' => 'Jaume', 'apellido' => 'Lavignole', 'email' => 'jlavignole@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 001'],
            ['nombre' => 'Isabel', 'apellido' => 'Vázquez', 'email' => 'ivazquez@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 002'],
            ['nombre' => 'Enrique', 'apellido' => 'Ruiz', 'email' => 'eruiz@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 003'],
            ['nombre' => 'María', 'apellido' => 'García', 'email' => 'mgarcia@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 004'],
            ['nombre' => 'Jorge', 'apellido' => 'Jiménez', 'email' => 'jjimenez@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 005'],
            ['nombre' => 'Patricia', 'apellido' => 'Núñez', 'email' => 'pnunez@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 006'],
            ['nombre' => 'Alejandro', 'apellido' => 'Moreno', 'email' => 'amoreno@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 007'],
            ['nombre' => 'Elena', 'apellido' => 'Vargas', 'email' => 'evargas@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 008'],
            ['nombre' => 'Sergio', 'apellido' => 'Navarro', 'email' => 'snavarro@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 009'],
            ['nombre' => 'Gloria', 'apellido' => 'Campos', 'email' => 'gcampos@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 010'],
            ['nombre' => 'Rafael', 'apellido' => 'Iglesias', 'email' => 'riglesias@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 011'],
            ['nombre' => 'Catalina', 'apellido' => 'Molina', 'email' => 'cmolina@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 012'],
            ['nombre' => 'Iago', 'apellido' => 'Vega', 'email' => 'ivega@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 013'],
            ['nombre' => 'Lorena', 'apellido' => 'Herrera', 'email' => 'lherrera@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 014'],
            ['nombre' => 'Víctor', 'apellido' => 'Gutierrez', 'email' => 'vgutierrez@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 015'],
            ['nombre' => 'Sandra', 'apellido' => 'Ramos', 'email' => 'sramos@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 016'],
            ['nombre' => 'Lucas', 'apellido' => 'Flores', 'email' => 'lflores@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 017'],
            ['nombre' => 'Valeria', 'apellido' => 'Cabrera', 'email' => 'vcabrera@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 018'],
            ['nombre' => 'Martín', 'apellido' => 'Ramírez', 'email' => 'mramirez@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 019'],
            ['nombre' => 'Sofía', 'apellido' => 'Cortés', 'email' => 'scortes@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 020'],
            ['nombre' => 'Andrés', 'apellido' => 'Soto', 'email' => 'asoto@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 021'],
            ['nombre' => 'Daniela', 'apellido' => 'Delgado', 'email' => 'ddelgado@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 022'],
            ['nombre' => 'Cristian', 'apellido' => 'Parra', 'email' => 'cparra@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 023'],
            ['nombre' => 'Natalia', 'apellido' => 'Castro', 'email' => 'ncastro@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 024'],
            ['nombre' => 'Guillermo', 'apellido' => 'Rojas', 'email' => 'grojas@spotstay.com', 'rol' => 'arrendador', 'tel' => '+34 601 111 025'],

            // INQUILINOS (15+)
            ['nombre' => 'David', 'apellido' => 'Suárez', 'email' => 'dsuarez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 001'],
            ['nombre' => 'Laura', 'apellido' => 'Martínez', 'email' => 'lmartinez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 002'],
            ['nombre' => 'Pablo', 'apellido' => 'López', 'email' => 'plopez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 003'],
            ['nombre' => 'Marta', 'apellido' => 'Sánchez', 'email' => 'msanchez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 004'],
            ['nombre' => 'Fernando', 'apellido' => 'Pérez', 'email' => 'fperez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 005'],
            ['nombre' => 'Amanda', 'apellido' => 'García', 'email' => 'agarcia@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 006'],
            ['nombre' => 'Juan', 'apellido' => 'González', 'email' => 'jgonzalez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 007'],
            ['nombre' => 'Victoria', 'apellido' => 'Rodríguez', 'email' => 'vrodriguez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 008'],
            ['nombre' => 'Pepe', 'apellido' => 'Fernández', 'email' => 'pfernandez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 009'],
            ['nombre' => 'Raquel', 'apellido' => 'Díez', 'email' => 'rdiez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 010'],
            ['nombre' => 'Tomás', 'apellido' => 'Herrera', 'email' => 'therrera@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 011'],
            ['nombre' => 'Irene', 'apellido' => 'Jiménez', 'email' => 'ijimenez@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 012'],
            ['nombre' => 'Andrés', 'apellido' => 'Molina', 'email' => 'amolina@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 013'],
            ['nombre' => 'Rocío', 'apellido' => 'Vega', 'email' => 'rvega@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 014'],
            ['nombre' => 'Roberto', 'apellido' => 'Mora', 'email' => 'rmora@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 015'],
            ['nombre' => 'Sergi', 'apellido' => 'Nebot', 'email' => 'snebot@spotstay.com', 'rol' => 'inquilino', 'tel' => '+34 602 222 016'],
        ];

        foreach ($usuarios as $data) {
            $usuario = Usuario::firstOrCreate(
                ['email_usuario' => $data['email']],
                [
                    'nombre_usuario' => $data['nombre'] . ' ' . $data['apellido'],
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
