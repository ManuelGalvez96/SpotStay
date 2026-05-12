<?php

namespace Database\Seeders;

use App\Models\UsuarioGestor;
use App\Services\CodigoGestorService;
use Illuminate\Database\Seeder;

class CodigoGestorSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todos los gestores
        $gestores = UsuarioGestor::all();
        
        foreach ($gestores as $gestor) {
            // Crear un código por gestor
            try {
                CodigoGestorService::crearCodigoParaGestor($gestor->id_usuario_gestor);
            } catch (\Exception $e) {
                // Si ya existe, continuar
                continue;
            }
        }
    }
}
