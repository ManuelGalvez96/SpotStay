<?php

namespace Database\Seeders;

use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use App\Models\Propiedad;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

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

        $admin = $admins->isEmpty() ? null : $admins->random();
        $alquilerCounter = 0;

        foreach ($propiedades as $propiedad) {
            $numInquilinos = rand(1, 2);

            for ($i = 0; $i < $numInquilinos; $i++) {
                $inquilino = $inquilinos->get($alquilerCounter % $inquilinos->count());

                if ($i === 0) {
                    $estado = 'activo';
                    $mesesPasados = rand(1, 6);
                    $fechaInicio = now()->subMonths($mesesPasados)->startOfMonth();
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(10, 18));
                } else {
                    $estadosSecundarios = ['activo', 'finalizado', 'cancelado'];
                    $estado = $estadosSecundarios[array_rand($estadosSecundarios)];
                    $fechaInicio = now()->subMonths(rand(2, 12))->startOfMonth();

                    if ($estado === 'finalizado') {
                        $fechaFin = $fechaInicio->copy()->addMonths(rand(3, 6));
                    } elseif ($estado === 'cancelado') {
                        $fechaFin = $fechaInicio->copy()->addMonths(rand(1, 3));
                    } else {
                        $fechaFin = $fechaInicio->copy()->addMonths(rand(10, 18));
                    }
                }

                $aprobado = $estado !== 'cancelado' ? $fechaInicio->copy()->subDays(rand(5, 15)) : null;

                Alquiler::updateOrCreate(
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

                $alquiler = Alquiler::where('id_propiedad_fk', $propiedad->id_propiedad)
                    ->where('id_inquilino_fk', $inquilino->id_usuario)
                    ->first();

                if ($alquiler) {
                    $this->generarCuotas($alquiler);
                }

                $alquilerCounter++;
            }
        }

        $arrendadoresQueAlquilan = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'arrendador');
        })->limit(5)->get();

        $propiedadesDeOtros = Propiedad::whereNotIn('id_arrendador_fk', $arrendadoresQueAlquilan->pluck('id_usuario'))->get();

        foreach ($arrendadoresQueAlquilan as $arrendador) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                $propiedad = $propiedadesDeOtros->random();

                $esActivo = $i === 0;
                $estado = $esActivo ? 'activo' : (rand(0, 1) ? 'finalizado' : 'cancelado');
                $fechaInicio = now()->subMonths(rand(1, 6))->startOfMonth();

                if ($estado === 'finalizado') {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(3, 6));
                } elseif ($estado === 'cancelado') {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(1, 3));
                } else {
                    $fechaFin = $fechaInicio->copy()->addMonths(rand(10, 18));
                }

                $aprobado = $estado !== 'cancelado' ? $fechaInicio->copy()->subDays(rand(5, 15)) : null;

                Alquiler::updateOrCreate(
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

                $alquiler = Alquiler::where('id_propiedad_fk', $propiedad->id_propiedad)
                    ->where('id_inquilino_fk', $arrendador->id_usuario)
                    ->first();

                if ($alquiler) {
                    $this->generarCuotas($alquiler);
                }
            }
        }

        $usuarioSnebot = Usuario::where('email_usuario', 'snebot@spotstay.com')->first();

        if ($usuarioSnebot) {
            $companeroAleatorio = Usuario::whereHas('roles', function ($q) {
                $q->where('slug_rol', 'inquilino');
            })->where('id_usuario', '<>', $usuarioSnebot->id_usuario)
              ->inRandomOrder()
              ->first();

            $propiedadCompartida = Propiedad::where('id_arrendador_fk', '<>', $usuarioSnebot->id_usuario)
                ->inRandomOrder()
                ->first();

            if ($propiedadCompartida) {
                $fechaInicio = now()->subMonth()->startOfMonth();
                $fechaFin = $fechaInicio->copy()->addYear();

                $inquilinosCompartidos = [$usuarioSnebot, $companeroAleatorio];

                foreach ($inquilinosCompartidos as $inquilino) {
                    Alquiler::updateOrCreate(
                        [
                            'id_propiedad_fk' => $propiedadCompartida->id_propiedad,
                            'id_inquilino_fk' => $inquilino->id_usuario,
                        ],
                        [
                            'fecha_inicio_alquiler' => $fechaInicio->format('Y-m-d'),
                            'fecha_fin_alquiler' => $fechaFin->format('Y-m-d'),
                            'estado_alquiler' => 'activo',
                            'id_admin_aprueba_fk' => $admin?->id_usuario,
                            'aprobado_alquiler' => $fechaInicio->copy()->subDays(5),
                            'creado_alquiler' => $fechaInicio->copy()->subDays(10),
                            'actualizado_alquiler' => now(),
                        ]
                    );

                    $alquiler = Alquiler::where('id_propiedad_fk', $propiedadCompartida->id_propiedad)
                        ->where('id_inquilino_fk', $inquilino->id_usuario)
                        ->first();

                    if ($alquiler) {
                        $this->generarCuotas($alquiler);
                    }
                }

                $propiedadCompartida->update(['estado_propiedad' => 'alquilada']);
            }
        }
    }

    private function generarCuotas(Alquiler $alquiler): void
    {
        if (!Schema::hasTable('tbl_alquiler_cuota')) {
            return;
        }

        $propiedad = Propiedad::where('id_propiedad', $alquiler->id_propiedad_fk)
            ->select('precio_propiedad')
            ->first();

        $importeBase = round((float) ($propiedad->precio_propiedad ?? 0), 2);
        if ($importeBase <= 0) {
            return;
        }

        $diaVencimiento = Carbon::parse((string) $alquiler->fecha_inicio_alquiler)->day;

        $inicio = Carbon::parse((string) $alquiler->fecha_inicio_alquiler);
        $mesCuotaInicial = $inicio->copy()->startOfMonth();

        $limite = $alquiler->fecha_fin_alquiler
            ? Carbon::parse((string) $alquiler->fecha_fin_alquiler)->startOfMonth()
            : $mesCuotaInicial->copy()->addMonths(11);

        if ($limite->lessThan($mesCuotaInicial)) {
            return;
        }

        $cursor = $mesCuotaInicial->copy();
        $ahora = Carbon::now();

        while ($cursor->lessThanOrEqualTo($limite)) {
            $fechaVencimiento = $cursor->copy()->addMonth()->day($diaVencimiento)->toDateString();
            $fechaVencimientoParsed = Carbon::parse($fechaVencimiento)->startOfDay();

            $estado = 'pendiente';
            $pagadoEn = null;

            if ($fechaVencimientoParsed->lt($ahora)) {
                $estado = 'pagado';
                $pagadoEn = $fechaVencimientoParsed->copy()->endOfDay();
            }

            AlquilerCuota::firstOrCreate(
                [
                    'id_alquiler_fk' => (int) $alquiler->id_alquiler,
                    'mes_cuota' => $cursor->copy()->toDateString(),
                ],
                [
                    'importe_base' => $importeBase,
                    'estado' => $estado,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'pagado_en' => $pagadoEn,
                ]
            );

            $cursor->addMonth();
        }
    }
}
