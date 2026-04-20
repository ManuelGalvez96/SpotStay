<?php

namespace Database\Seeders;

use App\Models\HistorialIncidencia;
use App\Models\Incidencia;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class HistorialIncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = ['abierta', 'en_progreso', 'resuelta', 'cerrada'];
        
        $incidencias = Incidencia::all();
        $usuarios = Usuario::all();
        
        foreach ($incidencias as $incidencia) {
            // Crear 2-3 registros de historial por incidencia
            $numRegistros = rand(2, 3);
            
            for ($i = 0; $i < $numRegistros; $i++) {
                $usuario = $usuarios->random();
                $nuevoEstado = $estados[rand(0, count($estados) - 1)];
                $comentario = match($i) {
                    0 => 'Se abrió el reporte de incidencia',
                    1 => 'El técnico comenzó el diagnóstico',
                    default => 'Se completó la reparación'
                };
                
                HistorialIncidencia::create([
                    'id_incidencia_fk' => $incidencia->id_incidencia,
                    'id_usuario_fk' => $usuario->id_usuario,
                    'comentario_historial' => $comentario,
                    'cambio_estado_historial' => $nuevoEstado,
                    'creado_historial' => now()->subDays($numRegistros - $i),
                    'actualizado_historial' => now()->subDays($numRegistros - $i),
                ]);
            }
        }
    }
}