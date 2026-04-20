<?php

namespace Database\Seeders;

use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SuscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $suscripciones = [
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'basico', 'max_propiedades' => 3],
            ['plan' => 'profesional', 'max_propiedades' => 10],
            ['plan' => 'premium', 'max_propiedades' => 30],
            ['plan' => 'basico', 'max_propiedades' => 3],
        ];

        $arrendadores = Usuario::whereHas('roles', function ($query) {
            $query->where('slug_rol', 'arrendador');
        })->limit(20)->pluck('id_usuario')->toArray();

        foreach ($suscripciones as $index => $data) {
            if (isset($arrendadores[$index])) {
                Suscripcion::firstOrCreate(
                    ['id_usuario_fk' => $arrendadores[$index], 'plan_suscripcion' => $data['plan']],
                    [
                        'plan_suscripcion' => $data['plan'],
                        'max_propiedades_suscripcion' => $data['max_propiedades'],
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