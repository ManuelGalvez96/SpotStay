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
        // 1. Obtener los planes reales de la base de datos
        $planBasicoArrendador = Plan::where('slug_plan', 'basico-arrendador')->first();
        $planProArrendador = Plan::where('slug_plan', 'pro-arrendador')->first();
        
        $planMiembroEstandar = Plan::where('slug_plan', 'miembro-estandar')->first();
        $planMiembroPremium = Plan::where('slug_plan', 'miembro-premium')->first();

        // 2. Asignar suscripciones a Arrendadores
        if ($planBasicoArrendador && $planProArrendador) {
            $arrendadores = Usuario::whereHas('roles', function ($query) {
                $query->where('slug_rol', 'arrendador');
            })->get();

            foreach ($arrendadores as $index => $arrendador) {
                // Alternar entre Pro y Básico
                $plan = ($index % 2 === 0) ? $planProArrendador : $planBasicoArrendador;
                
                Suscripcion::firstOrCreate(
                    ['id_usuario_fk' => $arrendador->id_usuario],
                    [
                        'plan_suscripcion' => mb_strtolower((string) $plan->slug_plan),
                        'id_plan_fk' => $plan->id_plan,
                        'max_propiedades_suscripcion' => (int) $plan->max_propiedades_plan,
                        'precio_pagado_suscripcion' => $plan->precio_plan,
                        'inicio_suscripcion' => now()->toDateString(),
                        'fin_suscripcion' => now()->addMonth()->toDateString(),
                        'estado_suscripcion' => 'activa',
                        'creado_suscripcion' => now(),
                        'actualizado_suscripcion' => now(),
                    ]
                );
            }
        }

        // 3. Asignar suscripciones a Miembros e Inquilinos
        if ($planMiembroEstandar && $planMiembroPremium) {
            $miembros = Usuario::whereHas('roles', function ($query) {
                $query->whereIn('slug_rol', ['miembro', 'inquilino']);
            })->get();

            foreach ($miembros as $index => $miembro) {
                // Alternar entre Premium y Estándar
                $plan = ($index % 2 === 0) ? $planMiembroPremium : $planMiembroEstandar;
                
                Suscripcion::firstOrCreate(
                    ['id_usuario_fk' => $miembro->id_usuario],
                    [
                        'plan_suscripcion' => mb_strtolower((string) $plan->slug_plan),
                        'id_plan_fk' => $plan->id_plan,
                        'max_propiedades_suscripcion' => (int) $plan->max_propiedades_plan,
                        'precio_pagado_suscripcion' => $plan->precio_plan,
                        'inicio_suscripcion' => now()->toDateString(),
                        'fin_suscripcion' => now()->addMonth()->toDateString(),
                        'estado_suscripcion' => 'activa',
                        'creado_suscripcion' => now(),
                        'actualizado_suscripcion' => now(),
                    ]
                );
            }
        }
    }
}