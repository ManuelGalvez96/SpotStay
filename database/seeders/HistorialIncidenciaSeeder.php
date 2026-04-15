<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistorialIncidenciaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('tbl_usuario')
            ->where('email_usuario', 'admin@spotstay.com')
            ->value('id_usuario');

        $gestorId = DB::table('tbl_usuario')
            ->where('email_usuario', 'miguel@spotstay.com')
            ->value('id_usuario');

        // Obtén todas las incidencias con sus datos
        $incidencias = DB::table('tbl_incidencia')
            ->select('id_incidencia', 'id_reporta_fk', 'estado_incidencia', 'creado_incidencia')
            ->get();

        foreach ($incidencias as $incidencia) {
            // Registro 1: Incidencia reportada (siempre)
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $incidencia->id_incidencia,
                'id_usuario_fk' => $incidencia->id_reporta_fk,
                'comentario_historial' => 'Incidencia reportada',
                'cambio_estado_historial' => 'abierta',
                'creado_historial' => $incidencia->creado_incidencia,
            ]);

            // Registro 2: Revisada por admin (siempre)
            DB::table('tbl_historial_incidencia')->insert([
                'id_incidencia_fk' => $incidencia->id_incidencia,
                'id_usuario_fk' => $adminId,
                'comentario_historial' => 'Revisada por administración',
                'cambio_estado_historial' => null,
                'creado_historial' => Carbon::parse($incidencia->creado_incidencia)->addHour(),
            ]);

            // Registro 3: Asignada a gestor (si en_proceso, resuelta o cerrada)
            if (in_array($incidencia->estado_incidencia, ['en_proceso', 'resuelta', 'cerrada'])) {
                DB::table('tbl_historial_incidencia')->insert([
                    'id_incidencia_fk' => $incidencia->id_incidencia,
                    'id_usuario_fk' => $gestorId,
                    'comentario_historial' => 'Incidencia asignada y en gestión',
                    'cambio_estado_historial' => 'en_proceso',
                    'creado_historial' => Carbon::parse($incidencia->creado_incidencia)->addHours(2),
                ]);
            }

            // Registro 4: Resuelta (si resuelta o cerrada)
            if (in_array($incidencia->estado_incidencia, ['resuelta', 'cerrada'])) {
                DB::table('tbl_historial_incidencia')->insert([
                    'id_incidencia_fk' => $incidencia->id_incidencia,
                    'id_usuario_fk' => $gestorId,
                    'comentario_historial' => 'Incidencia resuelta satisfactoriamente',
                    'cambio_estado_historial' => 'resuelta',
                    'creado_historial' => Carbon::parse($incidencia->creado_incidencia)->addDays(5),
                ]);
            }

            // Registro 5: Cerrada (si cerrada)
            if ($incidencia->estado_incidencia === 'cerrada') {
                DB::table('tbl_historial_incidencia')->insert([
                    'id_incidencia_fk' => $incidencia->id_incidencia,
                    'id_usuario_fk' => $adminId,
                    'comentario_historial' => 'Incidencia cerrada y archivada',
                    'cambio_estado_historial' => 'cerrada',
                    'creado_historial' => Carbon::parse($incidencia->creado_incidencia)->addDays(10),
                ]);
            }
        }
    }
}
