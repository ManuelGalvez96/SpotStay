<?php

namespace Database\Seeders;

use App\Models\Conversacion;
use App\Models\Propiedad;
use Illuminate\Database\Seeder;

class ConversacionSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = Propiedad::all();

        if ($propiedades->isEmpty()) {
            return;
        }

        $conversaciones = [
            // Conversaciones directas (sin propiedad)
            ['tipo_conversacion' => 'directa', 'id_propiedad_fk' => null],
            ['tipo_conversacion' => 'directa', 'id_propiedad_fk' => null],
            ['tipo_conversacion' => 'directa', 'id_propiedad_fk' => null],
            ['tipo_conversacion' => 'directa', 'id_propiedad_fk' => null],
            ['tipo_conversacion' => 'directa', 'id_propiedad_fk' => null],
        ];

        // Conversaciones de grupo (asociadas a propiedades)
        foreach ($propiedades as $propiedad) {
            $conversaciones[] = [
                'tipo_conversacion' => 'grupo',
                'id_propiedad_fk' => $propiedad->id_propiedad,
            ];
        }

        foreach ($conversaciones as $data) {
            Conversacion::firstOrCreate(
                ['tipo_conversacion' => $data['tipo_conversacion'], 'id_propiedad_fk' => $data['id_propiedad_fk']],
                [
                    'tipo_conversacion' => $data['tipo_conversacion'],
                    'id_propiedad_fk' => $data['id_propiedad_fk'],
                    'creado_conversacion' => now(),
                    'actualizado_conversacion' => now(),
                ]
            );
        }
    }
}