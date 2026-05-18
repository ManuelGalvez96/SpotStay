<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropiedadPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = DB::table('tbl_propiedad')
            ->select('id_propiedad', 'id_gestor_fk', 'estado_propiedad')
            ->whereNotNull('id_gestor_fk')
            ->get();

        foreach ($propiedades as $index => $propiedad) {
            DB::table('tbl_propiedad_permisos')->updateOrInsert(
                [
                    'id_propiedad_fk' => $propiedad->id_propiedad,
                    'id_gestor_fk' => $propiedad->id_gestor_fk,
                ],
                [
                    'incidencias' => in_array($propiedad->estado_propiedad, ['publicada', 'alquilada'], true) || $index % 2 === 0,
                    'gastos' => in_array($propiedad->estado_propiedad, ['publicada', 'alquilada'], true),
                    'chat' => $index % 3 !== 0,
                    'editar_propiedad' => $propiedad->estado_propiedad === 'borrador' || $index % 4 === 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}