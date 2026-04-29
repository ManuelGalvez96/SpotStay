<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GastoDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Localizar a Sergi
        $sergi = DB::table('tbl_usuario')->where('email_usuario', 'snebot@spotstay.com')->first();

        if (!$sergi) {
            return;
        }

        // 2. Localizar su alquiler activo para saber en qué propiedad vive
        $alquilerSergi = DB::table('tbl_alquiler')
            ->where('id_inquilino_fk', $sergi->id_usuario)
            ->where('estado_alquiler', 'activo')
            ->first();

        if (!$alquilerSergi) {
            return;
        }

        $propiedadId = $alquilerSergi->id_propiedad_fk;
        $gestorId = DB::table('tbl_propiedad')->where('id_propiedad', $propiedadId)->value('id_gestor_fk');

        // 3. Buscar a TODOS los inquilinos que viven en esa propiedad (compañeros de Sergi)
        $alquileresEnPropiedad = DB::table('tbl_alquiler')
            ->where('id_propiedad_fk', $propiedadId)
            ->where('estado_alquiler', 'activo')
            ->get();

        $numInquilinos = $alquileresEnPropiedad->count();
        if ($numInquilinos === 0) return;

        // 4. Crear Gastos de Prueba (Luz y Agua)
        $servicios = [
            ['concepto' => 'Luz Mensual', 'categoria' => 'luz', 'importe' => 90.00],
            ['concepto' => 'Agua Trimestral', 'categoria' => 'agua', 'importe' => 45.00],
        ];

        foreach ($servicios as $servicio) {
            // Cabecera en tbl_gasto
            $idGasto = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk'   => $propiedadId,
                'id_gestor_fk'      => $gestorId,
                'concepto_gasto'    => $servicio['concepto'],
                'categoria_gasto'   => $servicio['categoria'],
                'importe_estimado'  => $servicio['importe'],
                'ambito_gasto'      => 'propiedad',
                'estado_gasto'      => 'activo',
                'fecha_inicio_gasto' => now()->startOfMonth(),
                'fecha_fin_gasto'    => now()->addYear(),
                'creado_gasto'      => now(),
            ]);

            // Cuota en tbl_gasto_cuota
            $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
                'id_gasto_fk'           => $idGasto,
                'mes_cuota'             => now()->startOfMonth()->toDateString(),
                'vencimiento_cuota'     => now()->endOfMonth()->toDateString(),
                'importe_total_cuota'   => $servicio['importe'],
                'estado_cuota'          => 'pendiente',
                'creado_cuota'          => now(),
            ]);

            // Detalles individuales (Dividimos el importe entre todos los que viven allí)
            $importeIndividual = $servicio['importe'] / $numInquilinos;

            foreach ($alquileresEnPropiedad as $alq) {
                DB::table('tbl_gasto_cuota_detalle')->insert([
                    'id_gasto_cuota_fk' => $idCuota,
                    'id_alquiler_fk'    => $alq->id_alquiler,
                    'id_pagador_fk'     => $alq->id_inquilino_fk,
                    'importe_detalle'   => $importeIndividual,
                    'estado_detalle'    => 'pendiente',
                    'creado_detalle'    => now(),
                ]);
            }
        }
    }
}
