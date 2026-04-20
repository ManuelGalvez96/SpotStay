<?php

namespace Database\Seeders;

use App\Models\Conversacion;
use App\Models\Propiedad;
use Illuminate\Database\Seeder;

class ConversacionSeeder extends Seeder
{
    public function run(): void
    {
        $conversaciones = [
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 1],
            ['tipo' => 'grupo', 'propiedad_id' => 2],
            ['tipo' => 'grupo', 'propiedad_id' => 3],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 4],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 5],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 6],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 7],
            ['tipo' => 'directa', 'propiedad_id' => null],
            ['tipo' => 'grupo', 'propiedad_id' => 8],
            ['tipo' => 'directa', 'propiedad_id' => null],
        ];

        $propiedades = Propiedad::pluck('id_propiedad')->toArray();

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
                    ->whereRaw("TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad)) = ?", [$conv['propiedad_direccion']])
                    ->value('id_propiedad');
            }

            Conversacion::firstOrCreate(
                ['id_propiedad_fk' => $propiedadId, 'tipo_conversacion' => $data['tipo']],
                [
                    'creado_conversacion' => now(),
                    'actualizado_conversacion' => now(),
                ]
            );
        }
    }
}