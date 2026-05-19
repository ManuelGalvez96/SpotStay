<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GastoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $sergi = DB::table('tbl_usuario')->where('email_usuario', 'snebot@spotstay.com')->first();
        if (!$sergi) return;

        $alquileresSergi = DB::table('tbl_alquiler')
            ->where('id_inquilino_fk', $sergi->id_usuario)
            ->where('estado_alquiler', 'activo')
            ->get();

        if ($alquileresSergi->isEmpty()) return;

        foreach ($alquileresSergi as $alquilerSergi) {
            $propiedadId = $alquilerSergi->id_propiedad_fk;
            $gestorId = DB::table('tbl_propiedad')->where('id_propiedad', $propiedadId)->value('id_gestor_fk');

            $alquileresEnPropiedad = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $propiedadId)
                ->where('estado_alquiler', 'activo')
                ->get();

            $numInquilinos = $alquileresEnPropiedad->count();
            if ($numInquilinos === 0) continue;

            $esCompartida = $numInquilinos > 1;

            $servicios = [
                ['concepto' => 'Luz Mensual', 'categoria' => 'luz', 'importe_base' => 90.00],
                ['concepto' => 'Agua Trimestral', 'categoria' => 'agua', 'importe_base' => 45.00],
                ['concepto' => 'Reparación fontanería', 'categoria' => 'reparacion', 'importe_base' => 150.00],
                ['concepto' => 'Reparación eléctrica', 'categoria' => 'reparacion', 'importe_base' => 200.00],
                ['concepto' => 'Reparación cerrajería', 'categoria' => 'reparacion', 'importe_base' => 85.00],
            ];

            foreach ($servicios as $indiceServicio => $servicio) {
                $importeServicio = $servicio['importe_base'];

                if ($servicio['categoria'] === 'reparacion') {
                    $variacion = rand(-25, 40);
                    $importeServicio = max(35, round($servicio['importe_base'] + $variacion + (rand(0, 99) / 100), 2));
                }

                $idGasto = DB::table('tbl_gasto')->insertGetId([
                    'id_propiedad_fk'   => $propiedadId,
                    'id_gestor_fk'      => $gestorId,
                    'concepto_gasto'    => $servicio['concepto'],
                    'categoria_gasto'   => $servicio['categoria'],
                    'importe_estimado'  => $importeServicio,
                    'ambito_gasto'      => 'propiedad',
                    'estado_gasto'      => 'activo',
                    'fecha_inicio_gasto' => now()->startOfMonth(),
                    'fecha_fin_gasto'    => now()->addYear(),
                    'creado_gasto'      => now(),
                ]);

                if ($servicio['categoria'] === 'reparacion') {
                    $esVencida = (($indiceServicio + $propiedadId) % 2) === 0;
                    $vencimiento = $esVencida
                        ? now()->subDays(rand(5, 35))->toDateString()
                        : now()->addDays(rand(7, 30))->toDateString();
                } else {
                    $vencimiento = $esCompartida
                        ? now()->subDays(rand(3, 20))->toDateString()
                        : now()->addDays(rand(10, 30))->toDateString();
                }

                $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
                    'id_gasto_fk'           => $idGasto,
                    'mes_cuota'             => now()->startOfMonth()->toDateString(),
                    'vencimiento_cuota'     => $vencimiento,
                    'importe_total_cuota'   => $importeServicio,
                    'estado_cuota'          => 'pendiente',
                    'creado_cuota'          => now(),
                ]);

                $importeIndividual = round($importeServicio / $numInquilinos, 2);

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
}
