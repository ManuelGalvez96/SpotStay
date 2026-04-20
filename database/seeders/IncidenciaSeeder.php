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
            ['titulo' => 'Grifo con fugas', 'descripcion' => 'El grifo de la cocina tiene fugas', 'categoria' => 'mantenimiento', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Problema eléctrico', 'descripcion' => 'El enchufe de la sala no funciona', 'categoria' => 'electrico', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Cerradura rota', 'descripcion' => 'La puerta principal no cierra bien', 'categoria' => 'seguridad', 'prioridad' => 'alta', 'estado' => 'en_proceso'],
            ['titulo' => 'Pintura descascarada', 'descripcion' => 'La pintura de la pared del dormitorio está descascarada', 'categoria' => 'estetica', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Ventana rota', 'descripcion' => 'Una ventana del salón está rota', 'categoria' => 'danos', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Calefacción no funciona', 'descripcion' => 'La calefacción de la cocina no calienta', 'categoria' => 'climatizacion', 'prioridad' => 'media', 'estado' => 'en_proceso'],
            ['titulo' => 'Tuberías ruidosas', 'descripcion' => 'Las tuberías hacen ruido por las noches', 'categoria' => 'tuberias', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Acumulación de humedad', 'descripcion' => 'Hay humedad en las paredes del baño', 'categoria' => 'humedad', 'prioridad' => 'media', 'estado' => 'resuelto'],
            ['titulo' => 'Detector de humo averiado', 'descripcion' => 'El detector de humo no funciona correctamente', 'categoria' => 'seguridad', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Cortina rota', 'descripcion' => 'La persiana de la ventana no sube', 'categoria' => 'accesorios', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Baldosa levantada', 'descripcion' => 'Una baldosa del baño está levantada', 'categoria' => 'suelos', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Gotera en el techo', 'descripcion' => 'Hay una gotera en el techo del dormitorio', 'categoria' => 'filtraciones', 'prioridad' => 'alta', 'estado' => 'en_proceso'],
            ['titulo' => 'Puerta con desperfectos', 'descripcion' => 'La puerta del armario no cierra bien', 'categoria' => 'accesorios', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Espejo roto', 'descripcion' => 'El espejo del baño está roto', 'categoria' => 'danos', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Problemas de agua caliente', 'descripcion' => 'No hay agua caliente en el baño', 'categoria' => 'tuberias', 'prioridad' => 'alta', 'estado' => 'abierto'],
            ['titulo' => 'Zócalo suelto', 'descripcion' => 'El zócalo de la cocina está suelto', 'categoria' => 'estrutural', 'prioridad' => 'baja', 'estado' => 'resuelto'],
            ['titulo' => 'Luminaria rota', 'descripcion' => 'La lámpara del salón no funciona', 'categoria' => 'electrico', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Radiador frío', 'descripcion' => 'El radiador del dormitorio no calienta', 'categoria' => 'climatizacion', 'prioridad' => 'media', 'estado' => 'en_proceso'],
            ['titulo' => 'Suelo resbaladizo', 'descripcion' => 'El suelo del baño es muy resbaladizo', 'categoria' => 'seguridad', 'prioridad' => 'media', 'estado' => 'abierto'],
            ['titulo' => 'Mampara ducha rota', 'descripcion' => 'La puerta de la ducha está rota', 'categoria' => 'danos', 'prioridad' => 'media', 'estado' => 'resuelto'],
        ];

        foreach ($incidencias as $inc) {
            $idPropiedad = DB::table('tbl_propiedad')
                ->whereRaw("TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)) = ?", [$inc['propiedad_direccion']])
                ->value('id_propiedad');

        $propiedadIndex = 0;

        foreach ($incidencias as $data) {
            if ($propiedadIndex < count($propiedades)) {
                $propiedad = $propiedades[$propiedadIndex];
                $reportadorUsuario = $usuarios->random();
                $gestorAsignado = $gestores->isNotEmpty() ? $gestores->random() : null;

                Incidencia::firstOrCreate(
                    ['id_propiedad_fk' => $propiedad->id_propiedad, 'titulo_incidencia' => $data['titulo']],
                    [
                        'descripcion_incidencia' => $data['descripcion'],
                        'categoria_incidencia' => $data['categoria'],
                        'prioridad_incidencia' => $data['prioridad'],
                        'estado_incidencia' => $data['estado'],
                        'id_reporta_fk' => $reportadorUsuario->id_usuario,
                        'id_asignado_fk' => $gestorAsignado?->id_usuario,
                        'creado_incidencia' => now()->subDays(10),
                        'actualizado_incidencia' => now(),
                    ]
                );
                $propiedadIndex++;
            }
        }
    }
}