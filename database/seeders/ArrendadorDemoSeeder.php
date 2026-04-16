<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ArrendadorDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rolArrendador = DB::table('tbl_rol')->where('slug_rol', 'arrendador')->value('id_rol');
        $rolInquilino = DB::table('tbl_rol')->where('slug_rol', 'inquilino')->value('id_rol');

        $carlosId = $this->upsertUsuario(
            'Carlos García',
            'carlos@spotstay.com',
            '+34 611 222 333'
        );

        if ($rolArrendador) {
            DB::table('tbl_rol_usuario')->updateOrInsert(
                [
                    'id_usuario_fk' => $carlosId,
                    'id_rol_fk' => $rolArrendador,
                ],
                [
                    'asignado_rol_usuario' => Carbon::now(),
                ]
            );
        }

        $lauraId = $this->upsertUsuario(
            'Laura Martínez',
            'laura@spotstay.com',
            '+34 622 333 444'
        );

        if ($rolInquilino) {
            DB::table('tbl_rol_usuario')->updateOrInsert(
                [
                    'id_usuario_fk' => $lauraId,
                    'id_rol_fk' => $rolInquilino,
                ],
                [
                    'asignado_rol_usuario' => Carbon::now(),
                ]
            );
        }

        $pedroId = $this->upsertUsuario(
            'Pedro Sánchez',
            'pedro@spotstay.com',
            '+34 633 444 555'
        );

        if ($rolInquilino) {
            DB::table('tbl_rol_usuario')->updateOrInsert(
                [
                    'id_usuario_fk' => $pedroId,
                    'id_rol_fk' => $rolInquilino,
                ],
                [
                    'asignado_rol_usuario' => Carbon::now(),
                ]
            );
        }

        $propiedad1Id = $this->upsertPropiedad([
            'id_arrendador_fk' => $carlosId,
            'id_gestor_fk' => $carlosId,
            'titulo_propiedad' => 'Piso en Calle Mayor',
            'direccion_propiedad' => 'Calle Mayor 14',
            'ciudad_propiedad' => 'Madrid',
            'codigo_postal_propiedad' => '28001',
            'latitud_propiedad' => 40.4153,
            'longitud_propiedad' => -3.7074,
            'descripcion_propiedad' => 'Piso amplio en el centro de Madrid',
            'precio_propiedad' => 1200.00,
            'gastos_propiedad' => json_encode(['agua' => 30, 'luz' => 50, 'comunidad' => 40]),
            'estado_propiedad' => 'alquilada',
        ]);

        $propiedad2Id = $this->upsertPropiedad([
            'id_arrendador_fk' => $carlosId,
            'id_gestor_fk' => $carlosId,
            'titulo_propiedad' => 'Estudio Fuencarral',
            'direccion_propiedad' => 'Calle Fuencarral 22',
            'ciudad_propiedad' => 'Madrid',
            'codigo_postal_propiedad' => '28004',
            'latitud_propiedad' => 40.4211,
            'longitud_propiedad' => -3.7043,
            'descripcion_propiedad' => 'Estudio moderno en zona céntrica',
            'precio_propiedad' => 800.00,
            'gastos_propiedad' => json_encode(['agua' => 25, 'luz' => 40, 'comunidad' => 30]),
            'estado_propiedad' => 'publicada',
        ]);

        DB::table('tbl_alquiler')->updateOrInsert(
            [
                'id_propiedad_fk' => $propiedad1Id,
                'id_inquilino_fk' => $lauraId,
            ],
            [
                'id_admin_aprueba_fk' => DB::table('tbl_usuario')->where('email_usuario', 'admin@spotstay.com')->value('id_usuario'),
                'fecha_inicio_alquiler' => '2025-01-15',
                'fecha_fin_alquiler' => '2026-01-15',
                'estado_alquiler' => 'activo',
                'aprobado_alquiler' => Carbon::parse('2025-01-14 10:00:00'),
                'creado_alquiler' => Carbon::now(),
                'actualizado_alquiler' => Carbon::now(),
            ]
        );

        DB::table('tbl_alquiler')->updateOrInsert(
            [
                'id_propiedad_fk' => $propiedad2Id,
                'id_inquilino_fk' => $pedroId,
            ],
            [
                'id_admin_aprueba_fk' => DB::table('tbl_usuario')->where('email_usuario', 'admin@spotstay.com')->value('id_usuario'),
                'fecha_inicio_alquiler' => '2025-02-01',
                'fecha_fin_alquiler' => '2026-02-01',
                'estado_alquiler' => 'pendiente',
                'aprobado_alquiler' => null,
                'creado_alquiler' => Carbon::now(),
                'actualizado_alquiler' => Carbon::now(),
            ]
        );
    }

    private function upsertUsuario(string $nombre, string $email, string $telefono): int
    {
        DB::table('tbl_usuario')->updateOrInsert(
            ['email_usuario' => $email],
            [
                'nombre_usuario' => $nombre,
                'contrasena_usuario' => Hash::make('password123'),
                'telefono_usuario' => $telefono,
                'activo_usuario' => true,
                'creado_usuario' => Carbon::now(),
                'actualizado_usuario' => Carbon::now(),
            ]
        );

        return (int) DB::table('tbl_usuario')->where('email_usuario', $email)->value('id_usuario');
    }

    private function upsertPropiedad(array $data): int
    {
        DB::table('tbl_propiedad')->updateOrInsert(
            [
                'id_arrendador_fk' => $data['id_arrendador_fk'],
                'direccion_propiedad' => $data['direccion_propiedad'],
            ],
            [
                'id_gestor_fk' => $data['id_gestor_fk'],
                'titulo_propiedad' => $data['titulo_propiedad'],
                'ciudad_propiedad' => $data['ciudad_propiedad'],
                'codigo_postal_propiedad' => $data['codigo_postal_propiedad'],
                'latitud_propiedad' => $data['latitud_propiedad'],
                'longitud_propiedad' => $data['longitud_propiedad'],
                'descripcion_propiedad' => $data['descripcion_propiedad'],
                'precio_propiedad' => $data['precio_propiedad'],
                'gastos_propiedad' => $data['gastos_propiedad'],
                'estado_propiedad' => $data['estado_propiedad'],
                'creado_propiedad' => Carbon::now(),
                'actualizado_propiedad' => Carbon::now(),
            ]
        );

        return (int) DB::table('tbl_propiedad')
            ->where('id_arrendador_fk', $data['id_arrendador_fk'])
            ->where('direccion_propiedad', $data['direccion_propiedad'])
            ->value('id_propiedad');
    }
}
