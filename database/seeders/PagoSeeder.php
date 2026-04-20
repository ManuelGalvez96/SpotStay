<?php

namespace Database\Seeders;

use App\Models\Pago;
use App\Models\Alquiler;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $pagos = [
            ['monto' => 1500, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1500, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 2000, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1200, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1400, 'estado' => 'pendiente', 'tipo' => 'renta'],
            ['monto' => 1000, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1600, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 950, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1700, 'estado' => 'atrasado', 'tipo' => 'renta'],
            ['monto' => 1100, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 2200, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 2400, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1300, 'estado' => 'pendiente', 'tipo' => 'renta'],
            ['monto' => 1450, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 900, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1650, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1550, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1350, 'estado' => 'atrasado', 'tipo' => 'renta'],
            ['monto' => 1500, 'estado' => 'pagado', 'tipo' => 'renta'],
            ['monto' => 1800, 'estado' => 'pagado', 'tipo' => 'renta'],
        ];

        $alquileres = Alquiler::all();
        $usuarios = Usuario::all();

        foreach ($alquileres as $index => $alquiler) {
            if ($index < count($pagos)) {
                $data = $pagos[$index];
                $pagador = $usuarios->random();
                
                Pago::create([
                    'id_alquiler_fk' => $alquiler->id_alquiler,
                    'id_pagador_fk' => $pagador->id_usuario,
                    'tipo_pago' => $data['tipo'],
                    'concepto_pago' => 'Renta mensual',
                    'importe_pago' => $data['monto'],
                    'mes_pago' => now()->subMonths(rand(0, 3))->format('Y-m-01'),
                    'estado_pago' => $data['estado'],
                    'referencia_pago' => 'REF-' . $alquiler->id_alquiler . '-' . rand(1000, 9999),
                    'fecha_confirmacion_pago' => $data['estado'] === 'pagado' ? now()->subDays(rand(1, 10)) : null,
                    'creado_pago' => now(),
                    'actualizado_pago' => now(),
                ]);
            }
        }
    }
}