<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Inserta los 5 roles principales del sistema
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre_rol' => 'Administrador',
                'slug_rol' => 'admin',
                'creado_rol' => now(),
                'actualizado_rol' => now(),
            ],
            [
                'nombre_rol' => 'Arrendador',
                'slug_rol' => 'arrendador',
                'creado_rol' => now(),
                'actualizado_rol' => now(),
            ],
            [
                'nombre_rol' => 'Inquilino',
                'slug_rol' => 'inquilino',
                'creado_rol' => now(),
                'actualizado_rol' => now(),
            ],
            [
                'nombre_rol' => 'Gestor',
                'slug_rol' => 'gestor',
                'creado_rol' => now(),
                'actualizado_rol' => now(),
            ],
            [
                'nombre_rol' => 'Miembro',
                'slug_rol' => 'miembro',
                'creado_rol' => now(),
                'actualizado_rol' => now(),
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
