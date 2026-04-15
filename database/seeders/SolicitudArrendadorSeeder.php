<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolicitudArrendadorSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('tbl_usuario')
            ->where('email_usuario', 'admin@spotstay.com')
            ->value('id_usuario');

        // Obtén miembros (usuarios con rol miembro)
        $miembros = DB::table('tbl_rol_usuario')
            ->join('tbl_rol', 'tbl_rol.id_rol', '=', 'tbl_rol_usuario.id_rol_fk')
            ->join('tbl_usuario', 'tbl_usuario.id_usuario', '=', 'tbl_rol_usuario.id_usuario_fk')
            ->where('tbl_rol.slug_rol', 'miembro')
            ->select('tbl_usuario.id_usuario', 'tbl_usuario.nombre_usuario', 'tbl_usuario.email_usuario')
            ->get();

        // 9 solicitudes PENDIENTES
        $solicitudesPendientes = [
            [
                'usuario_email' => 'roberto.diaz@email.com',
                'usuario_nombre' => 'Roberto Díaz',
                'datos' => [
                    'direccion' => 'Calle Colón 8',
                    'ciudad' => 'Valencia',
                    'codigo_postal' => '46004',
                    'tipo' => 'Piso',
                    'precio_estimado' => 950,
                    'habitaciones' => 2,
                    'banos' => 1,
                    'tamano' => 65,
                    'descripcion' => 'Piso luminoso en el centro de Valencia'
                ],
                'creado' => Carbon::now()->subHours(2),
            ],
            [
                'usuario_email' => 'carmen.iglesias@email.com',
                'usuario_nombre' => 'Carmen Iglesias',
                'datos' => [
                    'direccion' => 'Alameda Principal 3',
                    'ciudad' => 'Sevilla',
                    'codigo_postal' => '41002',
                    'tipo' => 'Piso',
                    'precio_estimado' => 750,
                    'habitaciones' => 3,
                    'banos' => 2,
                    'tamano' => 80,
                    'descripcion' => 'Piso amplio en zona histórica de Sevilla'
                ],
                'creado' => Carbon::now()->subHours(5),
            ],
            [
                'usuario_email' => 'andres.molina@email.com',
                'usuario_nombre' => 'Andrés Molina',
                'datos' => [
                    'direccion' => 'Calle Fuencarral 22',
                    'ciudad' => 'Madrid',
                    'codigo_postal' => '28004',
                    'tipo' => 'Estudio',
                    'precio_estimado' => 650,
                    'habitaciones' => 1,
                    'banos' => 1,
                    'tamano' => 40,
                    'descripcion' => 'Estudio moderno en el centro de Madrid'
                ],
                'creado' => Carbon::now()->subDay(),
            ],
            [
                'usuario_email' => 'patricia.vega@email.com',
                'usuario_nombre' => 'Patricia Vega',
                'datos' => [
                    'direccion' => 'Gran Vía 45',
                    'ciudad' => 'Bilbao',
                    'codigo_postal' => '48001',
                    'tipo' => 'Ático',
                    'precio_estimado' => 1200,
                    'habitaciones' => 3,
                    'banos' => 2,
                    'tamano' => 90,
                    'descripcion' => 'Ático con terraza en el centro de Bilbao'
                ],
                'creado' => Carbon::now()->subDay(),
            ],
        ];

        // Añade 5 más del resto de miembros
        $ciudades = ['Barcelona', 'Madrid', 'Valencia', 'Málaga', 'Zaragoza'];
        $miembrosRestantes = $miembros->whereNotIn('email_usuario', [
            'roberto.diaz@email.com', 'carmen.iglesias@email.com',
            'andres.molina@email.com', 'patricia.vega@email.com'
        ])->take(5);

        foreach ($miembrosRestantes as $index => $miembro) {
            $solicitudesPendientes[] = [
                'usuario_email' => $miembro->email_usuario,
                'usuario_nombre' => $miembro->nombre_usuario,
                'datos' => [
                    'direccion' => 'Calle Principal ' . ($index + 1),
                    'ciudad' => $ciudades[$index % count($ciudades)],
                    'codigo_postal' => '28001',
                    'tipo' => 'Piso',
                    'precio_estimado' => rand(600, 1100),
                    'habitaciones' => rand(1, 3),
                    'banos' => rand(1, 2),
                    'tamano' => rand(40, 100),
                    'descripcion' => 'Piso disponible en zona céntrica'
                ],
                'creado' => Carbon::now()->subHours(rand(24, 96)),
            ];
        }

        // Inserta pendientes
        foreach ($solicitudesPendientes as $sol) {
            $idUsuario = DB::table('tbl_usuario')
                ->where('email_usuario', $sol['usuario_email'])
                ->value('id_usuario');

            DB::table('tbl_solicitud_arrendador')->insert([
                'id_usuario_fk' => $idUsuario,
                'datos_solicitud_arrendador' => json_encode($sol['datos']),
                'estado_solicitud_arrendador' => 'pendiente',
                'creado_solicitud_arrendador' => $sol['creado'],
            ]);
        }

        // 5 APROBADAS
        $solicitudesAprobadas = [
            [
                'datos' => [
                    'direccion' => 'Calle San Felipe 10',
                    'ciudad' => 'Barcelona',
                    'tipo' => 'Piso',
                    'precio_estimado' => 900
                ],
                'notas' => 'Documentación correcta. Solicitud aprobada.',
                'creado' => Carbon::now()->subWeeks(1),
            ],
            [
                'datos' => [
                    'direccion' => 'Avenida España 5',
                    'ciudad' => 'Madrid',
                    'tipo' => 'Piso',
                    'precio_estimado' => 1000
                ],
                'notas' => 'Documentación correcta. Solicitud aprobada.',
                'creado' => Carbon::now()->subWeeks(2),
            ],
            [
                'datos' => [
                    'direccion' => 'Calle Del Puerto 8',
                    'ciudad' => 'Valencia',
                    'tipo' => 'Piso',
                    'precio_estimado' => 800
                ],
                'notas' => 'Documentación correcta. Solicitud aprobada.',
                'creado' => Carbon::now()->subMonth(),
            ],
            [
                'datos' => [
                    'direccion' => 'Paseo Marítimo 15',
                    'ciudad' => 'Málaga',
                    'tipo' => 'Piso',
                    'precio_estimado' => 1100
                ],
                'notas' => 'Documentación correcta. Solicitud aprobada.',
                'creado' => Carbon::now()->subWeeks(3),
            ],
            [
                'datos' => [
                    'direccion' => 'Calle Mayor 20',
                    'ciudad' => 'Zaragoza',
                    'tipo' => 'Piso',
                    'precio_estimado' => 700
                ],
                'notas' => 'Documentación correcta. Solicitud aprobada.',
                'creado' => Carbon::now()->subMonth(),
            ],
        ];

        $miembrosAprobados = $miembros->slice(0, 5);
        foreach ($solicitudesAprobadas as $index => $sol) {
            if (isset($miembrosAprobados[$index])) {
                DB::table('tbl_solicitud_arrendador')->insert([
                    'id_usuario_fk' => $miembrosAprobados[$index]->id_usuario,
                    'datos_solicitud_arrendador' => json_encode($sol['datos']),
                    'estado_solicitud_arrendador' => 'aprobada',
                    'id_admin_revisa_fk' => $adminId,
                    'notas_solicitud_arrendador' => $sol['notas'],
                    'creado_solicitud_arrendador' => $sol['creado'],
                    'actualizado_solicitud_arrendador' => $sol['creado'],
                ]);
            }
        }

        // 3 RECHAZADAS
        $solicitudesRechazadas = [
            [
                'datos' => [
                    'direccion' => 'Calle Falsa 123',
                    'ciudad' => 'Córdoba',
                    'tipo' => 'Piso',
                    'precio_estimado' => 500
                ],
                'notas' => 'Documentación incompleta. Por favor adjunte DNI y nómina.',
                'creado' => Carbon::now()->subDays(3),
            ],
            [
                'datos' => [
                    'direccion' => 'Avenida Inexistente 99',
                    'ciudad' => 'Toledo',
                    'tipo' => 'Piso',
                    'precio_estimado' => 600
                ],
                'notas' => 'Dirección no verificable en nuestro sistema.',
                'creado' => Carbon::now()->subWeeks(1),
            ],
            [
                'datos' => [
                    'direccion' => 'Paseo Irreal 55',
                    'ciudad' => 'Segovia',
                    'tipo' => 'Piso',
                    'precio_estimado' => 10000
                ],
                'notas' => 'Precio fuera de rango de mercado para esa zona.',
                'creado' => Carbon::now()->subWeeks(2),
            ],
        ];

        $miembrosRechazados = $miembros->slice(5, 3);
        foreach ($solicitudesRechazadas as $index => $sol) {
            if (isset($miembrosRechazados[$index])) {
                DB::table('tbl_solicitud_arrendador')->insert([
                    'id_usuario_fk' => $miembrosRechazados[$index]->id_usuario,
                    'datos_solicitud_arrendador' => json_encode($sol['datos']),
                    'estado_solicitud_arrendador' => 'rechazada',
                    'id_admin_revisa_fk' => $adminId,
                    'notas_solicitud_arrendador' => $sol['notas'],
                    'creado_solicitud_arrendador' => $sol['creado'],
                    'actualizado_solicitud_arrendador' => $sol['creado'],
                ]);
            }
        }
    }
}
