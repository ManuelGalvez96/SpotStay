<?php

namespace Database\Seeders;

use App\Models\Incidencia;
use App\Models\Propiedad;
use App\Models\Alquiler;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class IncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = Propiedad::all();
        $gestores = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'gestor');
        })->get();

        if ($propiedades->isEmpty() || $gestores->isEmpty()) {
            return;
        }

        $incidenciasData = [
            ['titulo' => 'Grifo roto', 'descripcion' => 'El grifo de la cocina gotea constantemente', 'categoria' => 'fontanería', 'prioridad' => 'media'],
            ['titulo' => 'Calefacción averiada', 'descripcion' => 'La calefacción no funciona y hace frío en el interior', 'categoria' => 'calefacción', 'prioridad' => 'alta'],
            ['titulo' => 'Humedad en pared', 'descripcion' => 'Hay humedad en la pared del baño', 'categoria' => 'estructuración', 'prioridad' => 'media'],
            ['titulo' => 'Enchufe sin funcionar', 'descripcion' => 'Un enchufe de la sala no da corriente', 'categoria' => 'electricidad', 'prioridad' => 'alta'],
            ['titulo' => 'Ventana rota', 'descripcion' => 'Una ventana del dormitorio tiene una grieta', 'categoria' => 'carpintería', 'prioridad' => 'alta'],
            ['titulo' => 'Puerta atascada', 'descripcion' => 'La puerta del dormitorio cierra con dificultad', 'categoria' => 'carpintería', 'prioridad' => 'baja'],
            ['titulo' => 'Tuberías ruidosas', 'descripcion' => 'Las tuberías hacen ruido durante la noche', 'categoria' => 'fontanería', 'prioridad' => 'media'],
            ['titulo' => 'Radiador frío', 'descripcion' => 'El radiador del salón no calienta', 'categoria' => 'calefacción', 'prioridad' => 'media'],
            ['titulo' => 'Gotera en techo', 'descripcion' => 'Hay una gotera en el techo cuando llueve', 'categoria' => 'estructuración', 'prioridad' => 'alta'],
            ['titulo' => 'Lámpara rota', 'descripcion' => 'La lámpara de la sala no enciende', 'categoria' => 'electricidad', 'prioridad' => 'baja'],
            ['titulo' => 'Baldosa levantada', 'descripcion' => 'Una baldosa del baño está levantada', 'categoria' => 'revestimiento', 'prioridad' => 'media'],
            ['titulo' => 'Cerradura rota', 'descripcion' => 'La cerradura de la puerta principal no abre bien', 'categoria' => 'seguridad', 'prioridad' => 'alta'],
            ['titulo' => 'Sin agua caliente', 'descripcion' => 'El calentador de agua no funciona', 'categoria' => 'fontanería', 'prioridad' => 'alta'],
            ['titulo' => 'Pintura descascarada', 'descripcion' => 'La pintura de la pared está descascarada', 'categoria' => 'pintura', 'prioridad' => 'baja'],
            ['titulo' => 'Persiana rota', 'descripcion' => 'La persiana no sube correctamente', 'categoria' => 'carpintería', 'prioridad' => 'baja'],
            ['titulo' => 'Detector de humo averiado', 'descripcion' => 'El detector de humo no funciona', 'categoria' => 'seguridad', 'prioridad' => 'alta'],
            ['titulo' => 'Grieta en pared', 'descripcion' => 'Hay una grieta importante en la pared del salón', 'categoria' => 'estructuración', 'prioridad' => 'media'],
            ['titulo' => 'Zócalo suelto', 'descripcion' => 'El zócalo de la sala está despegado', 'categoria' => 'revestimiento', 'prioridad' => 'baja'],
            ['titulo' => 'Mampara ducha rota', 'descripcion' => 'El cristal de la ducha está roto', 'categoria' => 'revestimiento', 'prioridad' => 'media'],
            ['titulo' => 'Suelo mojado constantemente', 'descripcion' => 'El suelo del baño está siempre mojado', 'categoria' => 'fontanería', 'prioridad' => 'media'],
        ];

        $estados = ['abierta', 'en_proceso', 'resuelta'];
        $prioridades = ['baja', 'media', 'alta'];
        $incidenciaCounter = 0;

        foreach ($propiedades as $propiedad) {
            // Mínimo 1 incidencia por propiedad
            $numIncidencias = rand(1, 3);

            for ($i = 0; $i < $numIncidencias; $i++) {
                $incidenciaData = $incidenciasData[($incidenciaCounter + $i) % count($incidenciasData)];
                $estado = $estados[$incidenciaCounter % count($estados)];
                $prioridad = $prioridades[rand(0, count($prioridades) - 1)];

                // Obtener un inquilino de esa propiedad si existe
                $alquiler = Alquiler::where('id_propiedad_fk', $propiedad->id_propiedad)->first();
                $reportador = $alquiler ? Usuario::find($alquiler->id_inquilino_fk) : Usuario::whereHas('roles', function ($q) {
                    $q->where('slug_rol', 'inquilino');
                })->first();

                if (!$reportador) {
                    continue;
                }

                $asignado = $gestores->random();
                $fechaCreacion = now()->subDays(rand(1, 30));

                Incidencia::firstOrCreate(
                    [
                        'id_propiedad_fk' => $propiedad->id_propiedad,
                        'titulo_incidencia' => $incidenciaData['titulo'],
                    ],
                    [
                        'descripcion_incidencia' => $incidenciaData['descripcion'],
                        'categoria_incidencia' => $incidenciaData['categoria'],
                        'prioridad_incidencia' => $prioridad,
                        'estado_incidencia' => $estado,
                        'id_reporta_fk' => $reportador->id_usuario,
                        'id_asignado_fk' => $asignado->id_usuario,
                        'creado_incidencia' => $fechaCreacion,
                        'actualizado_incidencia' => $estado === 'resuelta' ? $fechaCreacion->copy()->addDays(rand(1, 10)) : now(),
                    ]
                );

                $incidenciaCounter++;
            }
        }
    }
}