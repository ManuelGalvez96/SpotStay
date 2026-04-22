<?php

namespace Database\Seeders;

use App\Models\Pago;
use App\Models\Alquiler;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $alquileres = Alquiler::where('estado_alquiler', 'activo')->get();
        $usuarios = Usuario::all();

        if ($alquileres->isEmpty() || $usuarios->isEmpty()) {
            return;
        }

        $estados = ['pagado', 'pendiente', 'atrasado'];
        $pagosCreados = 0;

        foreach ($alquileres as $alquiler) {
            // Crear pagos para últimos 3 meses
            for ($mesAtras = 0; $mesAtras < 3; $mesAtras++) {
                $estado = $estados[rand(0, count($estados) - 1)];
                $mesPago = now()->subMonths($mesAtras)->startOfMonth();

                // Buscar inquilino como pagador principal, sino usuario aleatorio
                $pagador = $usuarios->where('id_usuario', $alquiler->id_inquilino_fk)->first() ?? $usuarios->random();

                $pago = Pago::firstOrCreate(
                    [
                        'id_alquiler_fk' => $alquiler->id_alquiler,
                        'mes_pago' => $mesPago->format('Y-m-d'),
                    ],
                    [
                        'id_pagador_fk' => $pagador->id_usuario,
                        'tipo_pago' => 'alquiler',
                        'concepto_pago' => 'Renta mensual - ' . $mesPago->format('F Y'),
                        'importe_pago' => $alquiler->propiedad->precio_propiedad,
                        'estado_pago' => $estado,
                        'referencia_pago' => 'REF-' . $alquiler->id_alquiler . '-' . $mesPago->format('Ym'),
                        'fecha_confirmacion_pago' => $estado === 'pagado' ? $mesPago->addDays(rand(1, 10)) : null,
                        'creado_pago' => $mesPago->copy()->subDays(rand(1, 5)),
                        'actualizado_pago' => now(),
                    ]
                );

                $pagosCreados++;
            }
        }
    }
}