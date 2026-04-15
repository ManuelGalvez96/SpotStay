<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
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

            DB::table('tbl_contrato')->insert([
                'id_alquiler_fk' => $alquiler->id_alquiler,
                'url_pdf_contrato' => $url,
                'hash_contrato' => $hash,
                'firmado_arrendador' => true,
                'fecha_firma_arrendador' => $fechaInicio->subDay(),
                'ip_firma_arrendador' => '192.168.1.1',
                'firmado_inquilino' => true,
                'fecha_firma_inquilino' => $fechaInicio->subDay(),
                'ip_firma_inquilino' => '192.168.1.2',
                'estado_contrato' => 'firmado',
                'creado_contrato' => Carbon::now(),
            ]);
        }
    }
}
