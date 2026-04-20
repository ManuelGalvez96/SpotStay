<?php

namespace Database\Seeders;

use App\Models\Incidencia;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class IncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $incidencias = [
            ['titulo' => 'Grifo con fugas', 'descripcion' => 'El grifo de la cocina tiene fugas', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Problema eléctrico', 'descripcion' => 'El enchufe de la sala no funciona', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Cerradura rota', 'descripcion' => 'La puerta principal no cierra bien', 'prioridad' => 'alta', 'estado' => 'en_proceso'],
            ['titulo' => 'Pintura descascarada', 'descripcion' => 'La pintura de la pared está descascarada', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Ventana rota', 'descripcion' => 'Una ventana está rota', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Calefacción no funciona', 'descripcion' => 'La calefacción no calienta', 'prioridad' => 'media', 'estado' => 'en_proceso'],
            ['titulo' => 'Tuberías ruidosas', 'descripcion' => 'Las tuberías hacen ruido', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Humedad en paredes', 'descripcion' => 'Hay humedad en las paredes del baño', 'prioridad' => 'media', 'estado' => 'resuelto'],
            ['titulo' => 'Detector de humo averiado', 'descripcion' => 'El detector no funciona', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Persiana rota', 'descripcion' => 'La persiana no sube', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Baldosa levantada', 'descripcion' => 'Una baldosa del baño está levantada', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Gotera en techo', 'descripcion' => 'Hay una gotera en el techo', 'prioridad' => 'alta', 'estado' => 'en_proceso'],
            ['titulo' => 'Puerta con desperfectos', 'descripcion' => 'La puerta no cierra bien', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Espejo roto', 'descripcion' => 'El espejo del baño está roto', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Sin agua caliente', 'descripcion' => 'No hay agua caliente', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Zócalo suelto', 'descripcion' => 'El zócalo está suelto', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Lámpara rota', 'descripcion' => 'La lámpara no funciona', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Radiador frío', 'descripcion' => 'El radiador no calienta', 'prioridad' => 'media', 'estado' => 'en_proceso'],
            ['titulo' => 'Suelo resbaladizo', 'descripcion' => 'El suelo es muy resbaladizo', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Mampara ducha rota', 'descripcion' => 'La puerta de la ducha está rota', 'prioridad' => 'media', 'estado' => 'resuelto'],
        ];

        $propiedades = Propiedad::all();
        $usuarios = Usuario::all();

        if ($propiedades->isEmpty() || $usuarios->isEmpty()) {
            return;
        }

        $propIndex = 0;
        foreach ($incidencias as $data) {
            $propiedad = $propiedades->get($propIndex % $propiedades->count());
            $reportador = $usuarios->random();
            $asignado = $usuarios->random();
            $estado = $this->normalizarEstado((string) $data['estado']);
            $categoria = $this->inferirCategoria((string) $data['titulo'], (string) $data['descripcion']);

            Incidencia::firstOrCreate(
                ['id_propiedad_fk' => $propiedad->id_propiedad, 'titulo_incidencia' => $data['titulo']],
                [
                    'descripcion_incidencia' => $data['descripcion'],
                    'categoria_incidencia' => $categoria,
                    'prioridad_incidencia' => $data['prioridad'],
                    'estado_incidencia' => $estado,
                    'id_reporta_fk' => $reportador->id_usuario,
                    'id_asignado_fk' => $asignado?->id_usuario,
                    'creado_incidencia' => now()->subDays(rand(1, 30)),
                    'actualizado_incidencia' => now(),
                ]
            );

            $propIndex++;
        }
    }

    private function normalizarEstado(string $estado): string
    {
        return match (strtolower(trim($estado))) {
            'abierto' => 'abierta',
            'resuelto' => 'resuelta',
            default => strtolower(trim($estado)),
        };
    }

    private function inferirCategoria(string $titulo, string $descripcion): string
    {
        $texto = strtolower($titulo . ' ' . $descripcion);

        if (str_contains($texto, 'grifo') || str_contains($texto, 'tuber') || str_contains($texto, 'gotera') || str_contains($texto, 'agua') || str_contains($texto, 'ducha')) {
            return 'fontaneria';
        }

        if (str_contains($texto, 'enchufe') || str_contains($texto, 'electr') || str_contains($texto, 'lampara') || str_contains($texto, 'detector')) {
            return 'electricidad';
        }

        if (str_contains($texto, 'calefaccion') || str_contains($texto, 'radiador')) {
            return 'calefaccion';
        }

        return 'otro';
    }
}