<?php

namespace Database\Seeders;

use App\Models\Alquiler;
use App\Models\Propiedad;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AlquilerSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = Propiedad::where('estado_propiedad', 'alquilada')->get();
        $inquilinos = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'inquilino');
        })->get();

        $admins = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'admin');
        })->get();

        if ($propiedades->isEmpty() || $inquilinos->isEmpty()) {
            return;
        }

        $estados = ['activo', 'finalizado', 'cancelado'];
        $alquilerCounter = 0;

        foreach ($propiedades as $propiedad) {
            // Cada propiedad alquilada puede tener 1-3 inquilinos (para compartidas)
            $numInquilinos = rand(1, $propiedad->estado_propiedad === 'alquilada' ? 2 : 1);

            for ($i = 0; $i < $numInquilinos; $i++) {
                $inquilino = $inquilinos->get($alquilerCounter % $inquilinos->count());
                $admin = $admins->isEmpty() ? null : $admins->random();

                // Generar fechas coherentes
                $estado = $estados[$alquilerCounter % count($estados)];
                $fechaInicio = now()->subMonths(rand(1, 12))->startOfMonth();
                
                if ($estado === 'finalizado') {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(3, 6));
                } elseif ($estado === 'cancelado') {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(1, 3));
                } else {
                    $fechaFin = now()->addMonths(rand(2, 12));
                }

                $aprobado = $estado !== 'cancelado' ? $fechaInicio->copy()->subDays(rand(5, 15)) : null;

                Alquiler::firstOrCreate(
                    [
                        'id_propiedad_fk' => $propiedad->id_propiedad,
                        'id_inquilino_fk' => $inquilino->id_usuario,
                    ],
                    [
                        'fecha_inicio_alquiler' => $fechaInicio->format('Y-m-d'),
                        'fecha_fin_alquiler' => $fechaFin->format('Y-m-d'),
                        'estado_alquiler' => $estado,
                        'id_admin_aprueba_fk' => $admin?->id_usuario,
                        'aprobado_alquiler' => $aprobado,
                        'creado_alquiler' => $fechaInicio->copy()->subDays(rand(1, 10)),
                        'actualizado_alquiler' => now(),
                    ]
                );

                $alquilerCounter++;
            }
        }

        // Crear alquileres también para arrendadores que son inquilinos (alquilando de otros arrendadores)
        $arrendadoresQueAlquilan = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'arrendador');
        })->limit(5)->get();

        $propiedadesDeOtros = Propiedad::whereNotIn('id_arrendador_fk', $arrendadoresQueAlquilan->pluck('id_usuario'))->get();

        foreach ($arrendadoresQueAlquilan as $arrendador) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $propiedad = $propiedadesDeOtros->random();
                $admin = $admins->isEmpty() ? null : $admins->random();

                $estado = $estados[rand(0, count($estados) - 1)];
                $fechaInicio = now()->subMonths(rand(1, 12))->startOfMonth();
                
                if ($estado === 'finalizado') {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(3, 6));
                } else {
                    $fechaFin = now()->addMonths(rand(2, 12));
                }

                $aprobado = $estado !== 'cancelado' ? $fechaInicio->copy()->subDays(rand(5, 15)) : null;

                Alquiler::firstOrCreate(
                    [
                        'id_propiedad_fk' => $propiedad->id_propiedad,
                        'id_inquilino_fk' => $arrendador->id_usuario,
                    ],
                    [
                        'fecha_inicio_alquiler' => $fechaInicio->format('Y-m-d'),
                        'fecha_fin_alquiler' => $fechaFin->format('Y-m-d'),
                        'estado_alquiler' => $estado,
                        'id_admin_aprueba_fk' => $admin?->id_usuario,
                        'aprobado_alquiler' => $aprobado,
                        'creado_alquiler' => $fechaInicio->copy()->subDays(rand(1, 10)),
                        'actualizado_alquiler' => now(),
                    ]
                );
            }
        }
    }
}