<?php

namespace Database\Seeders;

use App\Models\Alquiler;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Sólo crear pagos para alquileres activos
        $alquileres = Alquiler::where('estado_alquiler', 'activo')->get();

        if ($alquileres->isEmpty()) {
            return;
        }

        // Cada alquiler tiene su "día de pago" propio para que no todos sean el mismo día.
        // Los días del 1 al 28 garantizan que existen en todos los meses.
        $diasDePago = [1, 5, 8, 10, 15, 18, 20, 22, 25, 28];
        $diaIndex = 0;

        foreach ($alquileres as $alquiler) {
            $inquilinoId = $alquiler->id_inquilino_fk;
            $precio = DB::table('tbl_propiedad')
                ->where('id_propiedad', $alquiler->id_propiedad_fk)
                ->value('precio_propiedad') ?? 1000;

            // Asignamos el día de pago de este alquiler de forma rotativa
            $diaPago = $diasDePago[$diaIndex % count($diasDePago)];
            $diaIndex++;

            // --- 1. Pagos históricos de los últimos 3 meses (estado: pagado) ---
            for ($i = 3; $i >= 1; $i--) {
                $mesPago = Carbon::now()->subMonths($i)->day(1)->format('Y-m-01');
                $fechaConfirmacion = Carbon::now()->subMonths($i)->day($diaPago);

                DB::table('tbl_pago')->insert([
                    'id_alquiler_fk'          => $alquiler->id_alquiler,
                    'id_pagador_fk'           => $inquilinoId,
                    'tipo_pago'               => 'renta',
                    'concepto_pago'           => 'Renta mensual ' . Carbon::now()->subMonths($i)->translatedFormat('F Y'),
                    'importe_pago'            => $precio,
                    'mes_pago'                => $mesPago,
                    'estado_pago'             => 'pagado',
                    'referencia_pago'         => 'REF-' . $alquiler->id_alquiler . '-' . strtoupper(substr(md5(rand()), 0, 6)),
                    'fecha_confirmacion_pago' => $fechaConfirmacion,
                    'creado_pago'             => now()->subMonths($i),
                    'actualizado_pago'        => now()->subMonths($i),
                ]);
            }

            // --- 2. Pago pendiente del próximo mes (el que mostrará el contador) ---
            // Calculamos el 1 del próximo mes y le sumamos los días extra para dar variedad
            // Ej: si diaPago = 15 → pago vence el 15 del mes que viene (min ~31 días)
            $fechaProximoPago = Carbon::now()->addMonth()->day(1)->format('Y-m-01');
            // La fecha "real" de vencimiento (para calcular días) usará el día asignado
            // pero el mes_pago siempre se guarda como primer día del mes (convención del seeder)

            DB::table('tbl_pago')->insert([
                'id_alquiler_fk'          => $alquiler->id_alquiler,
                'id_pagador_fk'           => $inquilinoId,
                'tipo_pago'               => 'renta',
                'concepto_pago'           => 'Renta mensual ' . Carbon::now()->addMonth()->translatedFormat('F Y'),
                'importe_pago'            => $precio,
                'mes_pago'                => $fechaProximoPago,
                'estado_pago'             => 'pendiente',
                'referencia_pago'         => 'REF-' . $alquiler->id_alquiler . '-' . strtoupper(substr(md5(rand()), 0, 6)),
                'fecha_confirmacion_pago' => null,
                'creado_pago'             => now(),
                'actualizado_pago'        => now(),
            ]);
        }
    }
}