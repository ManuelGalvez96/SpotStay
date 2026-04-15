<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtén alquileres activos con precio de propiedad
        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.id_inquilino_fk',
                'tbl_alquiler.fecha_inicio_alquiler',
                'tbl_propiedad.precio_mensual_propiedad'
            )
            ->get();

        foreach ($alquileres as $alquiler) {
            $precio = $alquiler->precio_mensual_propiedad;
            $fechaInicio = Carbon::parse($alquiler->fecha_inicio_alquiler);

            // Pago de fianza
            DB::table('tbl_pago')->insert([
                'id_alquiler_fk' => $alquiler->id_alquiler,
                'id_usuario_fk' => $alquiler->id_inquilino_fk,
                'tipo_pago' => 'fianza',
                'concepto_pago' => 'Fianza del alquiler',
                'importe_pago' => $precio * 2,
                'estado_pago' => 'confirmado',
                'referencia_pago' => 'pi_test_' . $alquiler->id_alquiler . '_fianza',
                'fecha_confirmacion_pago' => $fechaInicio->copy()->subDays(2),
                'creado_pago' => Carbon::now(),
            ]);

            // 3 pagos mensuales confirmados
            for ($i = 1; $i <= 3; $i++) {
                DB::table('tbl_pago')->insert([
                    'id_alquiler_fk' => $alquiler->id_alquiler,
                    'id_usuario_fk' => $alquiler->id_inquilino_fk,
                    'tipo_pago' => 'mensualidad',
                    'concepto_pago' => 'Alquiler mes ' . $i,
                    'importe_pago' => $precio,
                    'mes_pago' => $fechaInicio->copy()->addMonths($i)->toDateString(),
                    'estado_pago' => 'confirmado',
                    'referencia_pago' => 'pi_test_' . $alquiler->id_alquiler . '_mes' . $i,
                    'fecha_confirmacion_pago' => $fechaInicio->copy()->addMonths($i),
                    'creado_pago' => Carbon::now(),
                ]);
            }

            // 1 pago pendiente (mes actual)
            DB::table('tbl_pago')->insert([
                'id_alquiler_fk' => $alquiler->id_alquiler,
                'id_usuario_fk' => $alquiler->id_inquilino_fk,
                'tipo_pago' => 'mensualidad',
                'concepto_pago' => 'Alquiler mes actual',
                'importe_pago' => $precio,
                'mes_pago' => Carbon::now()->toDateString(),
                'estado_pago' => 'pendiente',
                'referencia_pago' => null,
                'fecha_confirmacion_pago' => null,
                'creado_pago' => Carbon::now(),
            ]);
        }
    }
}
