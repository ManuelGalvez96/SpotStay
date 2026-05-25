<?php

namespace App\Services;

use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class InquilinoFinanceService
{
    /**
     * Actualiza el estado de las cuotas de 'pendiente' a 'atrasado' si ha pasado el vencimiento.
     */
    public function actualizarCuotasAtrasadas(int $userId): void
    {
        AlquilerCuota::query()
            ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_alquiler_cuota.id_alquiler_fk')
            ->where('tbl_alquiler.id_inquilino_fk', $userId)
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->where('tbl_alquiler_cuota.estado', 'pendiente')
            ->whereDate('tbl_alquiler_cuota.fecha_vencimiento', '<', Carbon::today()->toDateString())
            ->update([
                'tbl_alquiler_cuota.estado' => 'atrasado',
                'tbl_alquiler_cuota.updated_at' => now(),
            ]);

        if (Schema::hasTable('tbl_gasto_cuota') && Schema::hasTable('tbl_gasto_cuota_detalle')) {
            DB::table('tbl_gasto_cuota_detalle')
                ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_gasto_cuota_detalle.id_alquiler_fk')
                ->where('tbl_alquiler.id_inquilino_fk', $userId)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->where('tbl_gasto_cuota_detalle.estado_detalle', 'pendiente')
                ->whereDate('tbl_gasto_cuota.vencimiento_cuota', '<', Carbon::today()->toDateString())
                ->update([
                    'tbl_gasto_cuota_detalle.estado_detalle' => 'atrasado',
                    'tbl_gasto_cuota_detalle.actualizado_detalle' => now(),
                ]);

            DB::table('tbl_gasto_cuota')
                ->join('tbl_gasto_cuota_detalle', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk', '=', 'tbl_gasto_cuota.id_gasto_cuota')
                ->join('tbl_alquiler', 'tbl_alquiler.id_alquiler', '=', 'tbl_gasto_cuota_detalle.id_alquiler_fk')
                ->where('tbl_alquiler.id_inquilino_fk', $userId)
                ->where('tbl_alquiler.estado_alquiler', 'activo')
                ->whereIn('tbl_gasto_cuota.estado_cuota', ['pendiente', 'parcial'])
                ->whereDate('tbl_gasto_cuota.vencimiento_cuota', '<', Carbon::today()->toDateString())
                ->update([
                    'tbl_gasto_cuota.estado_cuota' => 'atrasado',
                    'tbl_gasto_cuota.actualizado_cuota' => now(),
                ]);
        }
    }

    /**
     * Calcula el estado de pagos, deuda y fechas para un alquiler específico.
     */
    public function obtenerResumenPagoAlquiler(int $alquilerId, $fechaInicio = null): array
    {
        $hoy = Carbon::today();

        if (!$fechaInicio) {
            $alquiler = Alquiler::find($alquilerId);
            $fechaInicio = $alquiler?->fecha_inicio_alquiler;
        }

        $fechaInicio = Carbon::parse($fechaInicio ?? now());
        $diaInicio = (int) $fechaInicio->format('d');

        $mesVigente = $hoy->copy();
        if ((int) $hoy->format('d') < $diaInicio) {
            $mesVigente = $hoy->copy()->subMonth();
        }

        $cuotasAlquiler = AlquilerCuota::query()
            ->where('id_alquiler_fk', $alquilerId)
            ->orderBy('mes_cuota', 'asc')
            ->get();

        $deudaHastaHoy = $cuotasAlquiler->filter(function (AlquilerCuota $cuota) use ($mesVigente) {
            return in_array($cuota->estado, ['pendiente', 'atrasado']) &&
                Carbon::parse((string) $cuota->mes_cuota)->format('Y-m') <= $mesVigente->format('Y-m');
        });

        $estadoPagoActual = $deudaHastaHoy->isEmpty() ? 'pagado' : 'pendiente';

        $cuotaVigente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) {
            return in_array($cuota->estado, ['pendiente', 'atrasado']);
        });

        $cuotaReferencia = $cuotaVigente;

        if ($estadoPagoActual === 'pagado') {
            $proximaPendiente = $cuotasAlquiler->first(function (AlquilerCuota $cuota) use ($mesVigente) {
                return in_array($cuota->estado, ['pendiente', 'atrasado']) &&
                    Carbon::parse((string) $cuota->mes_cuota)->format('Y-m') > $mesVigente->format('Y-m');
            });
            if ($proximaPendiente) {
                $cuotaReferencia = $proximaPendiente;
            }
        }

        $diasParaPago = 0;
        $fechaProximoPago = $hoy->copy()->addMonth()->day($diaInicio);

        if ($cuotaReferencia) {
            $mesReferencia = Carbon::parse((string) $cuotaReferencia->mes_cuota);
            $fechaVencimiento = $mesReferencia->copy()->addMonth();
            $ultimoDiaMesDestino = (int) $fechaVencimiento->daysInMonth;
            $diaVencimientoEfectivo = min($diaInicio, $ultimoDiaMesDestino);
            $fechaVencimiento = $fechaVencimiento->day($diaVencimientoEfectivo)->endOfDay();

            $diasParaPago = Carbon::now()->diffInDays($fechaVencimiento, false);
            $diasParaPago = $diasParaPago < 0 ? 0 : (int) round($diasParaPago);
            $fechaProximoPago = $fechaVencimiento;
        }

        $cuotasPendientes = $cuotasAlquiler->filter(function (AlquilerCuota $c) {
            return in_array($c->estado, ['pendiente', 'atrasado']);
        });

        $numPagosAtrasados = $cuotasPendientes->filter(function (AlquilerCuota $c) use ($mesVigente) {
            return Carbon::parse((string) $c->mes_cuota)->format('Y-m') < $mesVigente->format('Y-m');
        })->count();

        $totalDeuda = (float) $cuotasPendientes->filter(function (AlquilerCuota $c) use ($mesVigente) {
            return Carbon::parse((string) $c->mes_cuota)->format('Y-m') <= $mesVigente->format('Y-m');
        })->sum('importe_base');

        if ($numPagosAtrasados > 0) {
            $estadoPagoActual = 'atrasado';
        }

        return [
            'estado_pago_actual' => $estadoPagoActual,
            'dias_para_pago' => $diasParaPago,
            'fecha_proximo_pago' => $fechaProximoPago,
            'num_pagos_atrasados' => $numPagosAtrasados,
            'total_deuda' => $totalDeuda,
            'cuota_pendiente_id' => $cuotaVigente?->id_alquiler_cuota,
            'cuota_pendiente_importe' => (float) ($cuotaVigente?->importe_base ?? 0),
        ];
    }

    /**
     * Obtiene un resumen completo de todos los gastos pendientes del inquilino.
     */
    public function obtenerResumenCompletoGastos(int $userId, ?int $idPropiedad = null, ?string $tipoGasto = null, ?string $nombreGasto = null): array
    {
        $queryAlquileres = Alquiler::where('id_inquilino_fk', $userId)->where('estado_alquiler', 'activo');
        
        if ($idPropiedad) {
            $queryAlquileres->where('id_propiedad_fk', $idPropiedad);
        }

        $alquileres = $queryAlquileres->get();
        
        $pendientes = collect();
        $totalDeuda = 0;
        $totalAtrasado = 0;
        $ahora = Carbon::now()->endOfMonth();

        foreach ($alquileres as $alquiler) {
            // Dividir importes entre compañeros de piso
            $numInquilinos = max(1, DB::table('tbl_alquiler')
                ->where('id_propiedad_fk', $alquiler->id_propiedad_fk)
                ->where('estado_alquiler', 'activo')
                ->count());

            // 1. Cuotas de Alquiler (Solo mes actual o anteriores)
            if (empty($tipoGasto) || strtolower($tipoGasto) === 'alquiler') {
                $cuotas = AlquilerCuota::where('id_alquiler_fk', $alquiler->id_alquiler)
                    ->whereIn('estado', ['pendiente', 'atrasado'])
                    ->whereDate('mes_cuota', '<=', $ahora)
                    ->get();

                foreach ($cuotas as $cuota) {
                    $concepto = 'Alquiler ' . Carbon::parse($cuota->mes_cuota)->translatedFormat('F Y');
                    if (!empty($nombreGasto) && stripos($concepto, $nombreGasto) === false) {
                        continue;
                    }

                    $importeIndividual = (float)$cuota->importe_base;
                    if ($numInquilinos > 1) {
                        $importeIndividual /= $numInquilinos;
                    }
                    $item = [
                        'id' => $cuota->id_alquiler_cuota,
                        'id_propiedad' => $alquiler->id_propiedad_fk,
                        'tipo' => 'alquiler',
                        'concepto' => $concepto,
                        'descripcion' => 'Mensualidad correspondiente al mes de ' . Carbon::parse($cuota->mes_cuota)->translatedFormat('F'),
                        'fecha_vencimiento' => $cuota->fecha_vencimiento,
                        'importe' => $importeIndividual,
                        'estado' => $cuota->estado,
                        'icono' => 'bi-house-door',
                        'color' => 'blue'
                    ];
                    $pendientes->push($item);
                    $totalDeuda += $item['importe'];
                    if ($item['estado'] === 'atrasado') $totalAtrasado += $item['importe'];
                }
            }

            // 2. Gastos/Suministros (Solo mes actual o anteriores)
            if (empty($tipoGasto) || !in_array(strtolower($tipoGasto), ['alquiler', 'reparacion'])) {
                if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
                    $queryGastos = DB::table('tbl_gasto_cuota_detalle')
                        ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                        ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                        ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', $alquiler->id_alquiler)
                        ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $userId)
                        ->where('tbl_gasto.categoria_gasto', '!=', 'reparacion')
                        ->whereIn('tbl_gasto_cuota_detalle.estado_detalle', ['pendiente', 'atrasado'])
                        ->whereDate('tbl_gasto_cuota.mes_cuota', '<=', $ahora)
                        ->select('tbl_gasto_cuota_detalle.*', 'tbl_gasto.concepto_gasto', 'tbl_gasto.categoria_gasto', 'tbl_gasto_cuota.vencimiento_cuota');

                    if (!empty($tipoGasto) && strtolower($tipoGasto) !== 'alquiler') {
                        $queryGastos->where('tbl_gasto.categoria_gasto', $tipoGasto);
                    }

                    if (!empty($nombreGasto)) {
                        $queryGastos->where('tbl_gasto.concepto_gasto', 'like', '%' . $nombreGasto . '%');
                    }

                    $gastos = $queryGastos->get();

                    foreach ($gastos as $gasto) {
                        $item = [
                            'id' => $gasto->id_gasto_cuota_detalle,
                            'id_propiedad' => $alquiler->id_propiedad_fk,
                            'tipo' => 'gasto',
                            'concepto' => $gasto->concepto_gasto ?? 'Gasto de Suministro',
                            'descripcion' => 'Recibo de ' . ($gasto->categoria_gasto ?? 'suministro'),
                            'fecha_vencimiento' => $gasto->vencimiento_cuota,
                            'importe' => (float)$gasto->importe_detalle,
                            'estado' => $gasto->estado_detalle,
                            'icono' => 'bi-lightning-charge',
                            'color' => 'yellow'
                        ];
                        $pendientes->push($item);
                        $totalDeuda += $item['importe'];
                        if ($item['estado'] === 'atrasado') $totalAtrasado += $item['importe'];
                    }
                }
            }

            // 3. Reparaciones pendientes creadas desde incidencias
            if (empty($tipoGasto) || strtolower($tipoGasto) === 'reparacion') {
                $queryReparaciones = DB::table('tbl_gasto_cuota_detalle')
                    ->join('tbl_gasto_cuota', 'tbl_gasto_cuota.id_gasto_cuota', '=', 'tbl_gasto_cuota_detalle.id_gasto_cuota_fk')
                    ->join('tbl_gasto', 'tbl_gasto.id_gasto', '=', 'tbl_gasto_cuota.id_gasto_fk')
                    ->where('tbl_gasto_cuota_detalle.id_alquiler_fk', $alquiler->id_alquiler)
                    ->where('tbl_gasto_cuota_detalle.id_pagador_fk', $userId)
                    ->where('tbl_gasto.categoria_gasto', 'reparacion')
                    ->whereIn('tbl_gasto_cuota_detalle.estado_detalle', ['pendiente', 'atrasado'])
                    ->whereDate('tbl_gasto_cuota.mes_cuota', '<=', $ahora);
                
                if (!empty($nombreGasto)) {
                    $queryReparaciones->where('tbl_gasto.concepto_gasto', 'like', '%' . $nombreGasto . '%');
                }

                $reparaciones = $queryReparaciones->get();

                foreach ($reparaciones as $reparacion) {
                    $item = [
                        'id' => $reparacion->id_gasto_cuota_detalle,
                        'id_propiedad' => $alquiler->id_propiedad_fk,
                        'tipo' => 'gasto',
                        'concepto' => $reparacion->concepto_gasto ?? 'Reparación pendiente',
                        'descripcion' => 'Cargo por reparación pendiente.',
                        'fecha_vencimiento' => $reparacion->vencimiento_cuota,
                        'importe' => (float)$reparacion->importe_detalle,
                        'estado' => $reparacion->estado_detalle,
                        'icono' => 'bi-tools',
                        'color' => 'purple',
                    ];
                    $pendientes->push($item);
                    $totalDeuda += $item['importe'];
                    if ($item['estado'] === 'atrasado') $totalAtrasado += $item['importe'];
                }
            }

            // 4. Incidencias esperando pago directo
            if (empty($tipoGasto) || strtolower($tipoGasto) === 'reparacion' || strtolower($tipoGasto) === 'incidencia') {
                $queryIncidencias = DB::table('tbl_incidencia')
                    ->where('id_propiedad_fk', $alquiler->id_propiedad_fk)
                    ->where('estado_incidencia', 'esperando_pago');
                
                if (!empty($nombreGasto)) {
                    $queryIncidencias->where('titulo_incidencia', 'like', '%' . $nombreGasto . '%');
                }

                $incidencias = $queryIncidencias->get();

                foreach ($incidencias as $incidencia) {
                    $item = [
                        'id' => $incidencia->id_incidencia,
                        'id_propiedad' => $alquiler->id_propiedad_fk,
                        'tipo' => 'incidencia',
                        'concepto' => 'Reparación: ' . $incidencia->titulo_incidencia,
                        'descripcion' => 'Presupuesto de reparación pendiente de pago.',
                        'fecha_vencimiento' => Carbon::parse($incidencia->actualizado_incidencia ?? now())->toDateString(),
                        'importe' => (float)($incidencia->presupuesto_importe_incidencia ?? 0),
                        'estado' => 'pendiente',
                        'icono' => 'bi-tools',
                        'color' => 'purple',
                    ];
                    $pendientes->push($item);
                    $totalDeuda += $item['importe'];
                }
            }
        }

        return [
            'total_pendiente' => $totalDeuda,
            'total_atrasado' => $totalAtrasado,
            'items' => $pendientes->sortBy('fecha_vencimiento')->values()->all()
        ];
    }
}
