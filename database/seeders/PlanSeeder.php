<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::where('activo_plan', true)->update(['activo_plan' => false]);
        $planes = [
            [
                'nombre_plan' => 'Básico Arrendador',
                'slug_plan' => 'basico-arrendador',
                'rol_destino' => 'arrendador',
                'precio_plan' => 9.99,
                'max_propiedades_plan' => 3,
                'descripcion_plan' => 'Plan básico con hasta 3 propiedades',
                'activo_plan' => true,
            ],
            [
                'nombre_plan' => 'Pro Arrendador',
                'slug_plan' => 'pro-arrendador',
                'rol_destino' => 'arrendador',
                'precio_plan' => 29.99,
                'max_propiedades_plan' => 10,
                'descripcion_plan' => 'Plan profesional con hasta 10 propiedades',
                'activo_plan' => true,
            ],
            [
                'nombre_plan' => 'Miembro Estándar',
                'slug_plan' => 'miembro-estandar',
                'rol_destino' => 'miembro',
                'precio_plan' => 0.00,
                'max_propiedades_plan' => 0,
                'descripcion_plan' => 'Acceso estándar con anuncios',
                'activo_plan' => true,
            ],
            [
                'nombre_plan' => 'Miembro Premium',
                'slug_plan' => 'miembro-premium',
                'rol_destino' => 'miembro',
                'precio_plan' => 4.99,
                'max_propiedades_plan' => 0,
                'descripcion_plan' => 'Experiencia sin anuncios y soporte prioritario',
                'activo_plan' => true,
            ],
        ];

        foreach ($planes as $data) {
            Plan::updateOrCreate(
                ['slug_plan' => $data['slug_plan']],
                $data
            );
        }
    }
}
