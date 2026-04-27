<?php

namespace Database\Seeders;

use App\Models\Suscripcion;
use App\Models\Plan;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $suscripciones = [
            ['plan' => 'Básico'],
            ['plan' => 'Básico'],
            ['plan' => 'Pro'],
            ['plan' => 'Pro'],
            ['plan' => 'Pro'],
            ['plan' => 'Profesional'],
            ['plan' => 'Profesional'],
            ['plan' => 'Básico'],
            ['plan' => 'Básico'],
            ['plan' => 'Pro'],
            ['plan' => 'Pro'],
            ['plan' => 'Profesional'],
            ['plan' => 'Profesional'],
            ['plan' => 'Básico'],
            ['plan' => 'Pro'],
            ['plan' => 'Profesional'],
            ['plan' => 'Básico'],
            ['plan' => 'Pro'],
            ['plan' => 'Profesional'],
            ['plan' => 'Básico'],
        ];

        $arrendadores = Usuario::whereHas('roles', function ($query) {
            $query->where('slug_rol', 'arrendador');
        })->limit(20)->pluck('id_usuario')->toArray();

        foreach ($suscripciones as $index => $data) {
            if (isset($arrendadores[$index])) {
                $nombrePlan = $data['plan'] === 'Profesional' ? 'Pro' : $data['plan'];
                $plan = Plan::where('nombre_plan', $nombrePlan)->first();
                
                if ($plan) {
                    $slugPlan = mb_strtolower((string) $plan->slug_plan);

                    Suscripcion::firstOrCreate(
                        ['id_usuario_fk' => $arrendadores[$index], 'id_plan_fk' => $plan->id_plan],
                        [
                            'plan_suscripcion' => $slugPlan,
                            'id_plan_fk' => $plan->id_plan,
                            'max_propiedades_suscripcion' => (int) $plan->max_propiedades_plan,
                            'precio_pagado_suscripcion' => $plan->precio_plan,
                            'inicio_suscripcion' => now()->toDateString(),
                            'fin_suscripcion' => now()->addYear()->toDateString(),
                            'estado_suscripcion' => 'activa',
                            'creado_suscripcion' => now(),
                            'actualizado_suscripcion' => now(),
                        ]
                    );
                }
            }
        }
    }
}