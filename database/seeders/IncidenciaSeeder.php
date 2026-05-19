<?php

namespace Database\Seeders;

use App\Models\Incidencia;
use App\Models\Propiedad;
use App\Models\Alquiler;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Mapeo de nombres string a IDs de categoría
        $mapeoCategoria = [
            'fontaneria' => DB::table('tbl_categoria')->where('nombre_categoria', 'Fontanería')->value('id_categoria'),
            'electricidad' => DB::table('tbl_categoria')->where('nombre_categoria', 'Electricidad')->value('id_categoria'),
            'calefaccion' => DB::table('tbl_categoria')->where('nombre_categoria', 'Calefacción')->value('id_categoria'),
            'climatizacion' => DB::table('tbl_categoria')->where('nombre_categoria', 'Climatización')->value('id_categoria'),
            'humedades' => DB::table('tbl_categoria')->where('nombre_categoria', 'Humedades')->value('id_categoria'),
            'cerrajeria' => DB::table('tbl_categoria')->where('nombre_categoria', 'Cerrajería')->value('id_categoria'),
            'otro' => DB::table('tbl_categoria')->where('nombre_categoria', 'Otro')->value('id_categoria'),
        ];

        $incidenciasData = [
            ['titulo' => 'Grifo roto', 'descripcion' => 'El grifo de la cocina gotea constantemente', 'categoria' => 'fontaneria', 'prioridad' => 'media'],
            ['titulo' => 'Calefacción averiada', 'descripcion' => 'La calefacción no funciona y hace frío en el interior', 'categoria' => 'calefaccion', 'prioridad' => 'alta'],
            ['titulo' => 'Humedad en pared', 'descripcion' => 'Hay humedad en la pared del baño', 'categoria' => 'humedades', 'prioridad' => 'media'],
            ['titulo' => 'Enchufe sin funcionar', 'descripcion' => 'Un enchufe de la sala no da corriente', 'categoria' => 'electricidad', 'prioridad' => 'alta'],
            ['titulo' => 'Ventana rota', 'descripcion' => 'Una ventana del dormitorio tiene una grieta', 'categoria' => 'otro', 'prioridad' => 'alta'],
            ['titulo' => 'Puerta atascada', 'descripcion' => 'La puerta del dormitorio cierra con dificultad', 'categoria' => 'cerrajeria', 'prioridad' => 'baja'],
            ['titulo' => 'Tuberías ruidosas', 'descripcion' => 'Las tuberías hacen ruido durante la noche', 'categoria' => 'fontaneria', 'prioridad' => 'media'],
            ['titulo' => 'Radiador frío', 'descripcion' => 'El radiador del salón no calienta', 'categoria' => 'calefaccion', 'prioridad' => 'media'],
            ['titulo' => 'Gotera en techo', 'descripcion' => 'Hay una gotera en el techo cuando llueve', 'categoria' => 'humedades', 'prioridad' => 'alta'],
            ['titulo' => 'Lámpara rota', 'descripcion' => 'La lámpara de la sala no enciende', 'categoria' => 'electricidad', 'prioridad' => 'baja'],
            ['titulo' => 'Baldosa levantada', 'descripcion' => 'Una baldosa del baño está levantada', 'categoria' => 'otro', 'prioridad' => 'media'],
            ['titulo' => 'Cerradura rota', 'descripcion' => 'La cerradura de la puerta principal no abre bien', 'categoria' => 'cerrajeria', 'prioridad' => 'alta'],
            ['titulo' => 'Sin agua caliente', 'descripcion' => 'El calentador de agua no funciona', 'categoria' => 'fontaneria', 'prioridad' => 'alta'],
            ['titulo' => 'Pintura descascarada', 'descripcion' => 'La pintura de la pared está descascarada', 'categoria' => 'otro', 'prioridad' => 'baja'],
            ['titulo' => 'Persiana rota', 'descripcion' => 'La persiana no sube correctamente', 'categoria' => 'otro', 'prioridad' => 'baja'],
            ['titulo' => 'Detector de humo averiado', 'descripcion' => 'El detector de humo no funciona', 'categoria' => 'otro', 'prioridad' => 'alta'],
            ['titulo' => 'Grieta en pared', 'descripcion' => 'Hay una grieta importante en la pared del salón', 'categoria' => 'humedades', 'prioridad' => 'media'],
            ['titulo' => 'Zócalo suelto', 'descripcion' => 'El zócalo de la sala está despegado', 'categoria' => 'otro', 'prioridad' => 'baja'],
            ['titulo' => 'Mampara ducha rota', 'descripcion' => 'El cristal de la ducha está roto', 'categoria' => 'otro', 'prioridad' => 'media'],
            ['titulo' => 'Suelo mojado constantemente', 'descripcion' => 'El suelo del baño está siempre mojado', 'categoria' => 'fontaneria', 'prioridad' => 'media'],
        ];

        $estados = ['abierta', 'esperando_decision', 'esperando_pago', 'solucionada', 'resuelta'];
        $prioridades = ['baja', 'media', 'alta', 'urgente'];
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
                    $incidenciaCounter++;
                    continue;
                }

                $asignado = $gestores->random();
                $fechaCreacion = now()->subDays(rand(1, 30));
                $idCategoriaFk = $mapeoCategoria[$incidenciaData['categoria']] ?? null;

                $presupuesto = null;
                $detallePresupuesto = null;
                $responsablePago = null;
                $pagadoPresupuesto = false;
                $pagadoIncidencia = null;

                if (in_array($estado, ['esperando_decision', 'esperando_pago', 'solucionada', 'resuelta'])) {
                    $presupuesto = rand(80, 600);
                    $detallePresupuesto = 'Reparación de materiales, mano de obra y desplazamiento.';
                }

                if (in_array($estado, ['esperando_pago', 'solucionada', 'resuelta'])) {
                    $responsablePago = rand(0, 1) ? 'arrendador' : 'inquilino';
                }

                if (in_array($estado, ['solucionada', 'resuelta'])) {
                    $pagadoPresupuesto = true;
                    $pagadoIncidencia = $fechaCreacion->copy()->addDays(rand(1, 5));
                }

                Incidencia::firstOrCreate(
                    [
                        'id_propiedad_fk' => $propiedad->id_propiedad,
                        'titulo_incidencia' => $incidenciaData['titulo'],
                    ],
                    [
                        'descripcion_incidencia' => $incidenciaData['descripcion'],
                        'id_categoria_fk' => $idCategoriaFk,
                        'prioridad_incidencia' => $prioridad,
                        'estado_incidencia' => $estado,
                        'id_reporta_fk' => $reportador->id_usuario,
                        'id_asignado_fk' => $asignado->id_usuario,
                        'presupuesto_importe_incidencia' => $presupuesto,
                        'detalle_presupuesto_incidencia' => $detallePresupuesto,
                        'responsable_pago_incidencia' => $responsablePago,
                        'pagado_presupuesto_incidencia' => $pagadoPresupuesto,
                        'pagado_incidencia' => $pagadoIncidencia,
                        'creado_incidencia' => $fechaCreacion,
                        'actualizado_incidencia' => in_array($estado, ['solucionada', 'resuelta']) ? $fechaCreacion->copy()->addDays(rand(1, 10)) : now(),
                    ]
                );

                $incidenciaCounter++;
            }
        }
    }
}