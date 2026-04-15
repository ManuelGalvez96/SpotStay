<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlquilerSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('tbl_usuario')
            ->where('email_usuario', 'admin@spotstay.com')
            ->value('id_usuario');

        $alquileres = [
            // Alquiler 1: Calle Mayor 14 - Laura
            [
                'propiedad_direccion' => 'Calle Mayor 14',
                'inquilino_email' => 'laura@spotstay.com',
                'fecha_inicio' => '2025-01-15',
                'fecha_fin' => '2026-01-15',
                'estado' => 'activo',
                'aprobado' => '2025-01-14 10:00:00',
            ],
            // Alquiler 2: Calle Serrano 47 - Pedro
            [
                'propiedad_direccion' => 'Calle Serrano 47',
                'inquilino_email' => 'pedro@spotstay.com',
                'fecha_inicio' => '2025-02-01',
                'fecha_fin' => '2026-02-01',
                'estado' => 'activo',
                'aprobado' => '2025-01-31 11:00:00',
            ],
            // Alquiler 3: Av. Diagonal 88 - Sofía
            [
                'propiedad_direccion' => 'Av. Diagonal 88',
                'inquilino_email' => 'sofia@spotstay.com',
                'fecha_inicio' => '2025-01-20',
                'fecha_fin' => '2026-01-20',
                'estado' => 'activo',
                'aprobado' => '2025-01-19 09:00:00',
            ],
            // Alquiler 4: Calle Pelai 12 - Carmen
            [
                'propiedad_direccion' => 'Calle Pelai 12',
                'inquilino_email' => 'carmen.iglesias@email.com',
                'fecha_inicio' => '2025-03-01',
                'fecha_fin' => '2026-03-01',
                'estado' => 'activo',
                'aprobado' => '2025-02-28 10:00:00',
            ],
            // Alquiler 5: Calle Mayor 14 - Andrés (segundo inquilino)
            [
                'propiedad_direccion' => 'Calle Mayor 14',
                'inquilino_email' => 'andres.molina@email.com',
                'fecha_inicio' => '2025-04-01',
                'fecha_fin' => '2026-04-01',
                'estado' => 'activo',
                'aprobado' => '2025-03-31 10:00:00',
            ],
            // Alquiler 6: Calle Fuencarral 22 - Patricia (pendiente)
            [
                'propiedad_direccion' => 'Calle Fuencarral 22',
                'inquilino_email' => 'patricia.vega@email.com',
                'fecha_inicio' => '2025-05-01',
                'fecha_fin' => '2026-05-01',
                'estado' => 'pendiente',
                'aprobado' => null,
            ],
            // Alquiler 7: Paseo de Gracia 5 - Javier (pendiente)
            [
                'propiedad_direccion' => 'Paseo de Gracia 5',
                'inquilino_email' => 'javier.moya@email.com',
                'fecha_inicio' => '2025-05-15',
                'fecha_fin' => '2026-05-15',
                'estado' => 'pendiente',
                'aprobado' => null,
            ],
            // Alquiler 8: Alameda de Hércules 3 - Lucía (pendiente)
            [
                'propiedad_direccion' => 'Alameda de Hércules 3',
                'inquilino_email' => 'lucia.serrano@email.com',
                'fecha_inicio' => '2025-06-01',
                'fecha_fin' => '2026-06-01',
                'estado' => 'pendiente',
                'aprobado' => null,
            ],
        ];

        foreach ($alquileres as $alq) {
            $idPropiedad = DB::table('tbl_propiedad')
                ->where('direccion_propiedad', $alq['propiedad_direccion'])
                ->value('id_propiedad');

            $idInquilino = DB::table('tbl_usuario')
                ->where('email_usuario', $alq['inquilino_email'])
                ->value('id_usuario');

            $data = [
                'id_propiedad_fk' => $idPropiedad,
                'id_inquilino_fk' => $idInquilino,
                'fecha_inicio_alquiler' => $alq['fecha_inicio'],
                'fecha_fin_alquiler' => $alq['fecha_fin'],
                'estado_alquiler' => $alq['estado'],
                'creado_alquiler' => Carbon::now(),
            ];

            if ($alq['estado'] === 'activo') {
                $data['id_admin_aprueba_fk'] = $adminId;
                $data['aprobado_alquiler'] = Carbon::parse($alq['aprobado']);
            }

            DB::table('tbl_alquiler')->insert($data);
        }
    }
}
