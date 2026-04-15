<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('tbl_usuario')->where('email_usuario', 'admin@spotstay.com')->value('id_usuario');
        $carlosId = DB::table('tbl_usuario')->where('email_usuario', 'carlos@spotstay.com')->value('id_usuario');
        $lauraId = DB::table('tbl_usuario')->where('email_usuario', 'laura@spotstay.com')->value('id_usuario');

        // 10 notificaciones para admin
        // 5 de nueva_solicitud
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'nueva_solicitud',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva solicitud de arrendador',
                'url' => '/admin/solicitudes',
                'nombre' => 'Roberto Díaz'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(2),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'nueva_solicitud',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva solicitud de arrendador',
                'url' => '/admin/solicitudes',
                'nombre' => 'Carmen Iglesias'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(5),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'nueva_solicitud',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva solicitud de arrendador',
                'url' => '/admin/solicitudes',
                'nombre' => 'Andrés Molina'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subDay(),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'nueva_solicitud',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva solicitud de arrendador',
                'url' => '/admin/solicitudes',
                'nombre' => 'Patricia Vega'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDay(),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'nueva_solicitud',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva solicitud de arrendador',
                'url' => '/admin/solicitudes',
                'nombre' => 'Javier Moya'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(2),
        ]);

        // 5 de alquiler_pendiente
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'alquiler_pendiente',
            'datos_notificacion' => json_encode([
                'titulo' => 'Alquiler pendiente de aprobación',
                'url' => '/admin/alquileres',
                'propiedad' => 'Calle Mayor 14'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(3),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'alquiler_pendiente',
            'datos_notificacion' => json_encode([
                'titulo' => 'Alquiler pendiente de aprobación',
                'url' => '/admin/alquileres',
                'propiedad' => 'Calle Fuencarral 22'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(8),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'alquiler_pendiente',
            'datos_notificacion' => json_encode([
                'titulo' => 'Alquiler pendiente de aprobación',
                'url' => '/admin/alquileres',
                'propiedad' => 'Paseo de Gracia 5'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(1),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'alquiler_pendiente',
            'datos_notificacion' => json_encode([
                'titulo' => 'Alquiler pendiente de aprobación',
                'url' => '/admin/alquileres',
                'propiedad' => 'Alameda de Hércules 3'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(2),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $adminId,
            'tipo_notificacion' => 'alquiler_pendiente',
            'datos_notificacion' => json_encode([
                'titulo' => 'Alquiler pendiente de aprobación',
                'url' => '/admin/alquileres',
                'propiedad' => 'Calle Mayor 14'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(3),
        ]);

        // 3 notificaciones para Carlos García
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $carlosId,
            'tipo_notificacion' => 'nueva_incidencia',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nueva incidencia en Calle Mayor 14',
                'propiedad' => 'Calle Mayor 14',
                'prioridad' => 'urgente'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(1),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $carlosId,
            'tipo_notificacion' => 'mensaje_nuevo',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nuevo mensaje de Laura Martínez',
                'url' => '/chat/1'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(2),
        ]);

        // 4 notificaciones para Laura Martínez
        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $lauraId,
            'tipo_notificacion' => 'alquiler_aprobado',
            'datos_notificacion' => json_encode([
                'titulo' => 'Tu alquiler ha sido aprobado',
                'propiedad' => 'Calle Mayor 14'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(2),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $lauraId,
            'tipo_notificacion' => 'mensaje_nuevo',
            'datos_notificacion' => json_encode([
                'titulo' => 'Nuevo mensaje de Carlos García'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subHours(1),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $lauraId,
            'tipo_notificacion' => 'pago_confirmado',
            'datos_notificacion' => json_encode([
                'titulo' => 'Pago de alquiler confirmado',
                'importe' => '1200'
            ]),
            'leida_notificacion' => true,
            'creado_notificacion' => Carbon::now()->subDays(5),
        ]);

        DB::table('tbl_notificacion')->insert([
            'id_usuario_fk' => $lauraId,
            'tipo_notificacion' => 'incidencia_actualizada',
            'datos_notificacion' => json_encode([
                'titulo' => 'Tu incidencia ha sido asignada a un gestor'
            ]),
            'leida_notificacion' => false,
            'creado_notificacion' => Carbon::now()->subDays(4),
        ]);

        // 3 notificaciones para otros usuarios
        $otrosUsuarios = DB::table('tbl_usuario')
            ->whereNotIn('email_usuario', ['admin@spotstay.com', 'carlos@spotstay.com', 'laura@spotstay.com'])
            ->limit(3)
            ->pluck('id_usuario')
            ->toArray();

        foreach ($otrosUsuarios as $index => $idUsuario) {
            $tiposNotif = ['mensaje_nuevo', 'pago_pendiente', 'incidencia_actualizada'];
            DB::table('tbl_notificacion')->insert([
                'id_usuario_fk' => $idUsuario,
                'tipo_notificacion' => $tiposNotif[$index % 3],
                'datos_notificacion' => json_encode([
                    'titulo' => 'Tienes una nueva notificación'
                ]),
                'leida_notificacion' => false,
                'creado_notificacion' => Carbon::now()->subHours(rand(1, 12)),
            ]);
        }
    }
}
