<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConversacionSeeder extends Seeder
{
    public function run(): void
    {
        // 4 conversaciones directas arrendador-inquilino
        $conversacionesDirectas = [
            [
                'tipo' => 'directa',
                'propiedad_direccion' => 'Calle Mayor 14',
                'usuarios' => [
                    'carlos@spotstay.com',
                    'laura@spotstay.com',
                ]
            ],
            [
                'tipo' => 'directa',
                'propiedad_direccion' => 'Calle Serrano 47',
                'usuarios' => [
                    'carlos@spotstay.com',
                    'pedro@spotstay.com',
                ]
            ],
            [
                'tipo' => 'directa',
                'propiedad_direccion' => 'Av. Diagonal 88',
                'usuarios' => [
                    'elena@spotstay.com',
                    'sofia@spotstay.com',
                ]
            ],
            [
                'tipo' => 'directa',
                'propiedad_direccion' => 'Calle Pelai 12',
                'usuarios' => [
                    'elena@spotstay.com',
                    'carmen.iglesias@email.com',
                ]
            ],
        ];

        // 2 conversaciones de grupo
        $conversacionesGrupo = [
            [
                'tipo' => 'grupo',
                'propiedad_direccion' => 'Paseo de Gracia 5',
                'usuarios' => [
                    'roberto.mora@spotstay.com',
                    'javier.moya@email.com',
                    'miguel@spotstay.com',
                ]
            ],
            [
                'tipo' => 'grupo',
                'propiedad_direccion' => 'Alameda de Hércules 3',
                'usuarios' => [
                    'roberto.mora@spotstay.com',
                    'lucia.serrano@email.com',
                    'miguel@spotstay.com',
                ]
            ],
        ];

        // 2 conversaciones miembro-arrendador
        $conversacionesMiembro = [
            [
                'tipo' => 'directa',
                'propiedad_direccion' => null,
                'usuarios' => [
                    'ana@spotstay.com',
                    'carlos@spotstay.com',
                ]
            ],
            [
                'tipo' => 'directa',
                'propiedad_direccion' => null,
                'usuarios' => [
                    'roberto.diaz@email.com',
                    'elena@spotstay.com',
                ]
            ],
        ];

        $todasConversaciones = array_merge(
            $conversacionesDirectas,
            $conversacionesGrupo,
            $conversacionesMiembro
        );

        foreach ($todasConversaciones as $conv) {
            $idPropiedad = null;
            if ($conv['propiedad_direccion']) {
                $idPropiedad = DB::table('tbl_propiedad')
                    ->where('direccion_propiedad', $conv['propiedad_direccion'])
                    ->value('id_propiedad');
            }

            $idConversacion = DB::table('tbl_conversacion')->insertGetId([
                'tipo_conversacion' => $conv['tipo'],
                'id_propiedad_fk' => $idPropiedad,
                'creado_conversacion' => Carbon::now(),
            ]);

            // Añade participantes
            foreach ($conv['usuarios'] as $email) {
                $idUsuario = DB::table('tbl_usuario')
                    ->where('email_usuario', $email)
                    ->value('id_usuario');

                DB::table('tbl_conversacion_usuario')->insert([
                    'id_conversacion_fk' => $idConversacion,
                    'id_usuario_fk' => $idUsuario,
                ]);
            }
        }
    }
}
