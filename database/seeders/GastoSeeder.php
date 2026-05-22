<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GastoSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = DB::table('tbl_propiedad')
            ->where('estado_propiedad', 'alquilada')
            ->whereNotNull('id_gestor_fk')
            ->get();

        if ($propiedades->isEmpty()) {
            return;
        }

        foreach ($propiedades as $propiedad) {
            $gestorId = (int) $propiedad->id_gestor_fk;

            $tienePermisoGastos = DB::table('tbl_propiedad_permisos')
                ->where('id_propiedad_fk', $propiedad->id_propiedad)
                ->where('id_gestor_fk', $gestorId)
                ->where('gastos', true)
                ->exists();

            if (!$tienePermisoGastos) {
                continue;
            }

            $alquileresActivos = DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $propiedad->id_propiedad)
                ->where('estado_alquiler', 'activo')
                ->select('id_alquiler', 'id_inquilino_fk')
                ->get();

            if ($alquileresActivos->isEmpty()) {
                continue;
            }

            $this->crearGastosRecurrentes($propiedad->id_propiedad, $gestorId, $alquileresActivos);

            $this->crearGastosIncidencia($propiedad->id_propiedad, $gestorId, $alquileresActivos);
        }
    }

    private function crearGastosRecurrentes(
        int $propiedadId,
        int $gestorId,
        $alquileresActivos
    ): void {
        $categorias = [
            ['categoria' => 'luz',       'concepto' => 'Electricidad mensual', 'importe_min' => 65, 'importe_max' => 95],
            ['categoria' => 'agua',      'concepto' => 'Agua mensual',         'importe_min' => 35, 'importe_max' => 55],
            ['categoria' => 'internet',  'concepto' => 'Fibra óptica',         'importe_min' => 35, 'importe_max' => 50],
            ['categoria' => 'comunidad', 'concepto' => 'Comunidad',            'importe_min' => 60, 'importe_max' => 100],
        ];

        $ahora          = Carbon::now();
        $mesesHistoricos = 4;
        $indicePropiedad = $propiedadId % 4;
        $tieneAtraso     = $indicePropiedad === 0;
        $mesAtraso       = $tieneAtraso ? rand(1, 3) : null;

        foreach ($categorias as $cat) {
            $importeBase = round(rand($cat['importe_min'] * 100, $cat['importe_max'] * 100) / 100, 2);

            $idGasto = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk'    => $propiedadId,
                'id_incidencia_fk'   => null,
                'id_gestor_fk'       => $gestorId,
                'concepto_gasto'     => $cat['concepto'],
                'categoria_gasto'    => $cat['categoria'],
                'importe_estimado'   => $importeBase,
                'ambito_gasto'       => 'propiedad',
                'pagador_gasto'      => 'inquilino',
                'periodicidad_gasto' => 'mensual',
                'dia_vencimiento'    => 10,
                'fecha_inicio_gasto' => $ahora->copy()->subMonths($mesesHistoricos)->startOfMonth()->toDateString(),
                'fecha_fin_gasto'    => $ahora->copy()->endOfYear()->toDateString(),
                'estado_gasto'       => 'activo',
                'creado_gasto'       => $ahora,
                'actualizado_gasto'  => $ahora,
            ]);

            for ($i = $mesesHistoricos; $i >= 0; $i--) {
                $mes         = $ahora->copy()->subMonths($i)->startOfMonth();
                $esMesActual = $i === 0;
                $esMesAtraso = $tieneAtraso && ($mesesHistoricos - $i) === $mesAtraso;
                $vencimiento = $mes->copy()->addMonth()->day(10);

                $estado     = 'pendiente';
                $pagadoCuota = null;

                if ($esMesAtraso) {
                    $estado = 'atrasado';
                } elseif (!$esMesActual) {
                    $estado      = 'pagado';
                    $pagadoCuota = $vencimiento->copy()->endOfDay();
                }

                $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
                    'id_gasto_fk'         => $idGasto,
                    'mes_cuota'           => $mes->toDateString(),
                    'vencimiento_cuota'   => $vencimiento->toDateString(),
                    'importe_total_cuota' => $importeBase,
                    'estado_cuota'        => $estado,
                    'pagado_cuota'        => $pagadoCuota,
                    'creado_cuota'        => $ahora,
                    'actualizado_cuota'   => $ahora,
                ]);

                $this->crearDetallesCuota($idCuota, $alquileresActivos, $importeBase, $estado, $pagadoCuota);
            }
        }
    }

    private function crearGastosIncidencia(
        int $propiedadId,
        int $gestorId,
        $alquileresActivos
    ): void {
        $incidenciasGenericas = [
            ['concepto' => 'Reparación de calefacción',   'importe_min' => 120, 'importe_max' => 200, 'pagador' => 'arrendador'],
            ['concepto' => 'Fontanería urgente',           'importe_min' => 85,  'importe_max' => 150, 'pagador' => 'inquilino'],
            ['concepto' => 'Reparación de persianas',      'importe_min' => 60,  'importe_max' => 120, 'pagador' => 'inquilino'],
            ['concepto' => 'Avería aire acondicionado',    'importe_min' => 150, 'importe_max' => 250, 'pagador' => 'arrendador'],
            ['concepto' => 'Sustitución termo eléctrico',  'importe_min' => 130, 'importe_max' => 220, 'pagador' => 'arrendador'],
        ];

        $numGenericos  = rand(1, 2);
        $seleccionados = array_rand($incidenciasGenericas, min($numGenericos, count($incidenciasGenericas)));
        $seleccionados = is_array($seleccionados) ? $seleccionados : [$seleccionados];

        $ahora = Carbon::now();

        // Gastos históricos genéricos (sin incidencia real — historial previo al sistema)
        foreach ($seleccionados as $idx) {
            $inc           = $incidenciasGenericas[$idx];
            $importe       = round(rand($inc['importe_min'] * 100, $inc['importe_max'] * 100) / 100, 2);
            $mesIncidencia = $ahora->copy()->subMonths(rand(1, 3))->startOfMonth();

            $idGasto = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk'    => $propiedadId,
                'id_incidencia_fk'   => null, // Sin incidencia real vinculada
                'id_gestor_fk'       => $gestorId,
                'concepto_gasto'     => $inc['concepto'],
                'categoria_gasto'    => 'reparacion',
                'importe_estimado'   => $importe,
                'ambito_gasto'       => 'propiedad',
                'pagador_gasto'      => $inc['pagador'],
                'periodicidad_gasto' => 'unica',
                'dia_vencimiento'    => 5,
                'fecha_inicio_gasto' => $mesIncidencia->toDateString(),
                'fecha_fin_gasto'    => $mesIncidencia->toDateString(),
                'estado_gasto'       => 'activo',
                'creado_gasto'       => $ahora,
                'actualizado_gasto'  => $ahora,
            ]);

            $vencimiento = $mesIncidencia->copy()->addMonth()->day(5);

            $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
                'id_gasto_fk'         => $idGasto,
                'mes_cuota'           => $mesIncidencia->toDateString(),
                'vencimiento_cuota'   => $vencimiento->toDateString(),
                'importe_total_cuota' => $importe,
                'estado_cuota'        => 'pagado',
                'pagado_cuota'        => $vencimiento->copy()->addDays(rand(0, 5))->endOfDay(),
                'creado_cuota'        => $ahora,
                'actualizado_cuota'   => $ahora,
            ]);

            $this->crearDetallesCuota($idCuota, $alquileresActivos, $importe, 'pagado', $vencimiento->copy()->addDays(rand(0, 5))->endOfDay(), $inc['pagador']);
        }

        // Gastos vinculados a incidencias REALES con presupuesto ya aprobado
        $incidenciasReales = DB::table('tbl_incidencia')
            ->where('id_propiedad_fk', $propiedadId)
            ->whereIn('estado_incidencia', ['esperando_pago', 'solucionada', 'resuelta'])
            ->whereNotNull('presupuesto_importe_incidencia')
            ->whereNotNull('responsable_pago_incidencia')
            ->get();

        foreach ($incidenciasReales as $incidencia) {
            $importe     = (float) $incidencia->presupuesto_importe_incidencia;
            $pagador     = $incidencia->responsable_pago_incidencia;
            $mesGasto    = Carbon::parse($incidencia->creado_incidencia)->startOfMonth();
            $estadoCuota = $incidencia->pagado_presupuesto_incidencia ? 'pagado' : 'pendiente';
            $pagadoCuota = $estadoCuota === 'pagado'
                ? Carbon::parse($incidencia->creado_incidencia)->addDays(rand(1, 5))->endOfDay()
                : null;

            $idGasto = DB::table('tbl_gasto')->insertGetId([
                'id_propiedad_fk'    => $propiedadId,
                'id_incidencia_fk'   => $incidencia->id_incidencia, // ← Trazabilidad real
                'id_gestor_fk'       => $gestorId,
                'concepto_gasto'     => 'Reparación: ' . $incidencia->titulo_incidencia,
                'categoria_gasto'    => 'reparacion',
                'importe_estimado'   => $importe,
                'ambito_gasto'       => 'propiedad',
                'pagador_gasto'      => $pagador,
                'periodicidad_gasto' => 'unica',
                'dia_vencimiento'    => 5,
                'fecha_inicio_gasto' => $mesGasto->toDateString(),
                'fecha_fin_gasto'    => $mesGasto->toDateString(),
                'estado_gasto'       => 'activo',
                'creado_gasto'       => $ahora,
                'actualizado_gasto'  => $ahora,
            ]);

            $vencimiento = $mesGasto->copy()->addMonth()->day(5);

            $idCuota = DB::table('tbl_gasto_cuota')->insertGetId([
                'id_gasto_fk'         => $idGasto,
                'mes_cuota'           => $mesGasto->toDateString(),
                'vencimiento_cuota'   => $vencimiento->toDateString(),
                'importe_total_cuota' => $importe,
                'estado_cuota'        => $estadoCuota,
                'pagado_cuota'        => $pagadoCuota,
                'creado_cuota'        => $ahora,
                'actualizado_cuota'   => $ahora,
            ]);

            $this->crearDetallesCuota($idCuota, $alquileresActivos, $importe, $estadoCuota, $pagadoCuota, $pagador);
        }
    }

    private function crearDetallesCuota(
        int $idCuota,
        $alquileresActivos,
        float $importeTotal,
        string $estadoCuota,
        ?Carbon $pagadoCuota = null,
        string $pagadorGasto = 'inquilino'
    ): void {
        $ahora = Carbon::now();

        if ($pagadorGasto === 'arrendador') {
            $primerAlquiler = $alquileresActivos->first();
            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk'  => $idCuota,
                'id_alquiler_fk'     => $primerAlquiler->id_alquiler,
                'id_pagador_fk'      => $primerAlquiler->id_inquilino_fk,
                'importe_detalle'    => round($importeTotal, 2),
                'estado_detalle'     => $estadoCuota === 'atrasado' ? 'pendiente' : $estadoCuota,
                'pagado_detalle'     => $pagadoCuota,
                'creado_detalle'     => $ahora,
                'actualizado_detalle'=> $ahora,
            ]);
            return;
        }

        $totalInquilinos = max(1, $alquileresActivos->count());
        $base            = floor(($importeTotal / $totalInquilinos) * 100) / 100;
        $acumulado       = 0.0;

        foreach ($alquileresActivos->values() as $index => $alquiler) {
            $importeDetalle = $index === $totalInquilinos - 1
                ? round($importeTotal - $acumulado, 2)
                : round($base, 2);

            $acumulado += $importeDetalle;

            DB::table('tbl_gasto_cuota_detalle')->insert([
                'id_gasto_cuota_fk'  => $idCuota,
                'id_alquiler_fk'     => $alquiler->id_alquiler,
                'id_pagador_fk'      => $alquiler->id_inquilino_fk,
                'importe_detalle'    => $importeDetalle,
                'estado_detalle'     => $estadoCuota === 'atrasado' ? 'pendiente' : $estadoCuota,
                'pagado_detalle'     => $pagadoCuota,
                'creado_detalle'     => $ahora,
                'actualizado_detalle'=> $ahora,
            ]);
        }
    }
}
