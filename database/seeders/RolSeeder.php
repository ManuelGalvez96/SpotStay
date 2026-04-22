<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre_rol' => 'Administrador', 'slug_rol' => 'admin'],
            ['nombre_rol' => 'Arrendador', 'slug_rol' => 'arrendador'],
            ['nombre_rol' => 'Inquilino', 'slug_rol' => 'inquilino'],
            ['nombre_rol' => 'Gestor', 'slug_rol' => 'gestor'],
            ['nombre_rol' => 'Miembro', 'slug_rol' => 'miembro'],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['slug_rol' => $rol['slug_rol']], $rol);
        }
    }
}
