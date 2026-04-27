<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre_plan' => 'Gratuito',
                'slug_plan' => 'gratuito',
                'precio_plan' => 0.00,
                'max_propiedades_plan' => 1,
                'descripcion_plan' => 'Plan gratuito con acceso limitado a 1 propiedad',
                'activo_plan' => true,
            ],
            [
                'nombre_plan' => 'Básico',
                'slug_plan' => 'basico',
                'precio_plan' => 9.99,
                'max_propiedades_plan' => 3,
                'descripcion_plan' => 'Plan básico con hasta 3 propiedades',
                'activo_plan' => true,
            ],
            [
                'nombre_plan' => 'Pro',
                'slug_plan' => 'pro',
                'precio_plan' => 29.99,
                'max_propiedades_plan' => 10,
                'descripcion_plan' => 'Plan profesional con hasta 10 propiedades',
                'activo_plan' => true,
            ],
        ];

        foreach ($planes as $data) {
            Plan::firstOrCreate(
                ['slug_plan' => $data['slug_plan']],
                $data
            );
        }
    }
}
