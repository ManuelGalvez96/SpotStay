<?php

namespace App\Traits;

use App\Models\Alquiler;
use App\Models\AlquilerCuota;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

trait InquilinoCalculosFinancieros
{
    /**
     * Actualiza el estado de las cuotas de 'pendiente' a 'atrasado' si ha pasado el vencimiento.
     */
    private function actualizarCuotasAtrasadas(int $userId): void
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
    private function obtenerResumenPagoAlquiler(int $alquilerId, $fechaInicio = null): array
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
}
