<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        $columnas = $this->obtenerColumnasContrato();

        // Obtén alquileres activos con sus propiedades relacionadas
        $alquileres = DB::table('tbl_alquiler')
            ->join('tbl_propiedad', 'tbl_propiedad.id_propiedad', '=', 'tbl_alquiler.id_propiedad_fk')
            ->join('tbl_usuario as arrendador', 'arrendador.id_usuario', '=', 'tbl_propiedad.id_arrendador_fk')
            ->join('tbl_usuario as inquilino', 'inquilino.id_usuario', '=', 'tbl_alquiler.id_inquilino_fk')
            ->where('tbl_alquiler.estado_alquiler', 'activo')
            ->select(
                'tbl_alquiler.id_alquiler',
                'tbl_alquiler.fecha_inicio_alquiler',
                'arrendador.id_usuario as id_arrendador',
                'inquilino.id_usuario as id_inquilino'
            )
            ->get();

        foreach ($alquileres as $alquiler) {
            $url = 'contratos/contrato_' . $alquiler->id_alquiler . '.pdf';
            $hash = hash('sha256', $url);
            $fechaInicio = Carbon::parse($alquiler->fecha_inicio_alquiler);

            $datosContrato = [
                'id_alquiler_fk' => $alquiler->id_alquiler,
                'hash_contrato' => $hash,
                'estado_contrato' => 'firmado',
                'creado_contrato' => Carbon::now(),
            ];

            if ($columnas['url']) {
                $datosContrato[$columnas['url']] = $url;
            }

            if ($columnas['firmado_arrendador']) {
                $datosContrato[$columnas['firmado_arrendador']] = true;
            }

            if ($columnas['fecha_firma_arrendador']) {
                $datosContrato[$columnas['fecha_firma_arrendador']] = $fechaInicio->copy()->subDay();
            }

            if ($columnas['ip_firma_arrendador']) {
                $datosContrato[$columnas['ip_firma_arrendador']] = '192.168.1.1';
            }

            if ($columnas['firmado_inquilino']) {
                $datosContrato[$columnas['firmado_inquilino']] = true;
            }

            if ($columnas['fecha_firma_inquilino']) {
                $datosContrato[$columnas['fecha_firma_inquilino']] = $fechaInicio->copy()->subDay();
            }

            if ($columnas['ip_firma_inquilino']) {
                $datosContrato[$columnas['ip_firma_inquilino']] = '192.168.1.2';
            }

            if (Schema::hasColumn('tbl_contrato', 'actualizado_contrato')) {
                $datosContrato['actualizado_contrato'] = Carbon::now();
            }

            DB::table('tbl_contrato')->insert($datosContrato);
        }
    }

    private function obtenerColumnasContrato(): array
    {
        return [
            'url' => $this->resolverColumna('url_pdf_contrato', 'url_contrato'),
            'firmado_arrendador' => $this->resolverColumna('firmado_arrendador', 'firmado_arrendador_contrato'),
            'fecha_firma_arrendador' => $this->resolverColumna('fecha_firma_arrendador', 'fecha_firma_arrendador_contrato'),
            'ip_firma_arrendador' => $this->resolverColumna('ip_firma_arrendador', 'ip_firma_arrendador_contrato'),
            'firmado_inquilino' => $this->resolverColumna('firmado_inquilino', 'firmado_inquilino_contrato'),
            'fecha_firma_inquilino' => $this->resolverColumna('fecha_firma_inquilino', 'fecha_firma_inquilino_contrato'),
        ];
    }

    private function resolverColumna(string $primaria, string $alterna): ?string
    {
        if (Schema::hasColumn('tbl_contrato', $primaria)) {
            return $primaria;
        }

        if (Schema::hasColumn('tbl_contrato', $alterna)) {
            return $alterna;
        }

        return null;
    }
}
