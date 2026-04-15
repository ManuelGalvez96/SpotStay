<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('tbl_usuario')
            ->where('email_usuario', 'admin@spotstay.com')
            ->value('id_usuario');

        $gestorId = DB::table('tbl_usuario')
            ->where('email_usuario', 'miguel@spotstay.com')
            ->value('id_usuario');

        $incidencias = [
            // ABIERTAS
            [
                'propiedad_direccion' => 'Calle Mayor 14',
                'reporta_email' => 'laura@spotstay.com',
                'titulo' => 'Fuga de agua en el baño',
                'descripcion' => 'Agua saliendo por debajo del inodoro desde esta mañana',
                'categoria' => 'fontaneria',
                'prioridad' => 'urgente',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subHours(1),
            ],
            [
                'propiedad_direccion' => 'Calle Serrano 47',
                'reporta_email' => 'pedro@spotstay.com',
                'titulo' => 'Sin calefacción en el piso',
                'descripcion' => 'La caldera no enciende desde ayer por la mañana',
                'categoria' => 'calefaccion',
                'prioridad' => 'alta',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subHours(5),
            ],
            [
                'propiedad_direccion' => 'Calle Serrano 47',
                'reporta_email' => 'sofia@spotstay.com',
                'titulo' => 'Persiana rota en habitación principal',
                'descripcion' => 'La persiana no sube ni baja, el mecanismo está roto',
                'categoria' => 'otro',
                'prioridad' => 'media',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subDay(),
            ],
            [
                'propiedad_direccion' => 'Av. Diagonal 88',
                'reporta_email' => 'pedro@spotstay.com',
                'titulo' => 'Cortocircuito en cocina',
                'descripcion' => 'Saltan los plomos al conectar cualquier electrodoméstico',
                'categoria' => 'electricidad',
                'prioridad' => 'urgente',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subHours(3),
            ],
            [
                'propiedad_direccion' => 'Calle Pelai 12',
                'reporta_email' => 'carmen.iglesias@email.com',
                'titulo' => 'Humedad en pared del salón',
                'descripcion' => 'Hay una mancha de humedad que va creciendo',
                'categoria' => 'otro',
                'prioridad' => 'alta',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subDays(2),
            ],
            [
                'propiedad_direccion' => 'Calle Fuencarral 22',
                'reporta_email' => 'andres.molina@email.com',
                'titulo' => 'Grifo de cocina gotea constantemente',
                'descripcion' => 'El grifo de la cocina gotea sin parar desde hace días',
                'categoria' => 'fontaneria',
                'prioridad' => 'baja',
                'estado' => 'abierta',
                'asignado' => null,
                'creado' => Carbon::now()->subDays(3),
            ],
            // EN PROCESO
            [
                'propiedad_direccion' => 'Paseo de Gracia 5',
                'reporta_email' => 'javier.moya@email.com',
                'titulo' => 'Rotura de tubería principal',
                'descripcion' => 'Fuga importante de agua en la cocina',
                'categoria' => 'fontaneria',
                'prioridad' => 'urgente',
                'estado' => 'en_proceso',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(2),
            ],
            [
                'propiedad_direccion' => 'Alameda de Hércules 3',
                'reporta_email' => 'lucia.serrano@email.com',
                'titulo' => 'Caldera sin presión',
                'descripcion' => 'La caldera no tiene presión y no funciona',
                'categoria' => 'calefaccion',
                'prioridad' => 'alta',
                'estado' => 'en_proceso',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(3),
            ],
            [
                'propiedad_direccion' => 'Calle Mayor 14',
                'reporta_email' => 'laura@spotstay.com',
                'titulo' => 'Instalación eléctrica deficiente',
                'descripcion' => 'Los enchufes no funcionan correctamente en dos habitaciones',
                'categoria' => 'electricidad',
                'prioridad' => 'alta',
                'estado' => 'en_proceso',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(4),
            ],
            [
                'propiedad_direccion' => 'Av. Diagonal 88',
                'reporta_email' => 'sofia@spotstay.com',
                'titulo' => 'Aire acondicionado no enfría',
                'descripcion' => 'El aire acondicionado funciona pero no enfría',
                'categoria' => 'otro',
                'prioridad' => 'media',
                'estado' => 'en_proceso',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(5),
            ],
            [
                'propiedad_direccion' => 'Calle Pelai 12',
                'reporta_email' => 'carmen.iglesias@email.com',
                'titulo' => 'Puerta de entrada difícil de cerrar',
                'descripcion' => 'La puerta principal cuesta mucho cerrar',
                'categoria' => 'otro',
                'prioridad' => 'media',
                'estado' => 'en_proceso',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(6),
            ],
            // RESUELTAS
            [
                'propiedad_direccion' => 'Calle Mayor 14',
                'reporta_email' => 'laura@spotstay.com',
                'titulo' => 'Fuga en grifo del baño',
                'descripcion' => 'El grifo del baño gotea',
                'categoria' => 'fontaneria',
                'prioridad' => 'media',
                'estado' => 'resuelta',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(10),
            ],
            [
                'propiedad_direccion' => 'Calle Serrano 47',
                'reporta_email' => 'pedro@spotstay.com',
                'titulo' => 'Persiana averiada en salón',
                'descripcion' => 'La persiana del salón no funciona',
                'categoria' => 'otro',
                'prioridad' => 'baja',
                'estado' => 'resuelta',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(12),
            ],
            [
                'propiedad_direccion' => 'Av. Diagonal 88',
                'reporta_email' => 'sofia@spotstay.com',
                'titulo' => 'Humedad en techo de cocina',
                'descripcion' => 'Mancha de humedad en el techo',
                'categoria' => 'otro',
                'prioridad' => 'alta',
                'estado' => 'resuelta',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(15),
            ],
            [
                'propiedad_direccion' => 'Calle Pelai 12',
                'reporta_email' => 'carmen.iglesias@email.com',
                'titulo' => 'Sin agua caliente',
                'descripcion' => 'No hay agua caliente en ninguna parte de la casa',
                'categoria' => 'otro',
                'prioridad' => 'alta',
                'estado' => 'resuelta',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subDays(18),
            ],
            // CERRADAS
            [
                'propiedad_direccion' => 'Calle Fuencarral 22',
                'reporta_email' => 'patricia.vega@email.com',
                'titulo' => 'Cerradura rota en puerta principal',
                'descripcion' => 'La cerradura de la puerta principal está rota',
                'categoria' => 'otro',
                'prioridad' => 'alta',
                'estado' => 'cerrada',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subWeeks(2),
            ],
            [
                'propiedad_direccion' => 'Paseo de Gracia 5',
                'reporta_email' => 'javier.moya@email.com',
                'titulo' => 'Radiador con ruidos',
                'descripcion' => 'El radiador hace ruido extraño',
                'categoria' => 'calefaccion',
                'prioridad' => 'media',
                'estado' => 'cerrada',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subWeeks(3),
            ],
            [
                'propiedad_direccion' => 'Alameda de Hércules 3',
                'reporta_email' => 'lucia.serrano@email.com',
                'titulo' => 'Interruptor roto en pasillo',
                'descripcion' => 'El interruptor del pasillo está roto',
                'categoria' => 'electricidad',
                'prioridad' => 'baja',
                'estado' => 'cerrada',
                'asignado' => 'miguel@spotstay.com',
                'creado' => Carbon::now()->subMonth(),
            ],
        ];

        foreach ($incidencias as $inc) {
            $idPropiedad = DB::table('tbl_propiedad')
                ->where('direccion_propiedad', $inc['propiedad_direccion'])
                ->value('id_propiedad');

            $idReporta = DB::table('tbl_usuario')
                ->where('email_usuario', $inc['reporta_email'])
                ->value('id_usuario');

            $idAsignado = null;
            if ($inc['asignado']) {
                $idAsignado = DB::table('tbl_usuario')
                    ->where('email_usuario', $inc['asignado'])
                    ->value('id_usuario');
            }

            DB::table('tbl_incidencia')->insert([
                'titulo_incidencia' => $inc['titulo'],
                'descripcion_incidencia' => $inc['descripcion'],
                'categoria_incidencia' => $inc['categoria'],
                'prioridad_incidencia' => $inc['prioridad'],
                'estado_incidencia' => $inc['estado'],
                'id_propiedad_fk' => $idPropiedad,
                'id_reporta_fk' => $idReporta,
                'id_asignado_fk' => $idAsignado,
                'creado_incidencia' => $inc['creado'],
            ]);
        }
    }
}
