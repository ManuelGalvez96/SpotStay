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

        foreach ($conversaciones as $data) {
            $propiedadId = null;
            if ($data['propiedad_id'] && isset($propiedades[$data['propiedad_id'] - 1])) {
                $propiedadId = $propiedades[$data['propiedad_id'] - 1];
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