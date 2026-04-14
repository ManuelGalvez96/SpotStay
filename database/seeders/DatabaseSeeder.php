<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders en orden correcto para respetar las foreign keys
     */
    public function run(): void
    {
        // 1. Crear roles
        $this->call(RolSeeder::class);

        // 2. Crear usuarios y asignar roles
        $this->call(UsuarioSeeder::class);
    }
}
