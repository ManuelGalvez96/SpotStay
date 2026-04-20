<?php

namespace Database\Seeders;

use App\Models\Alquiler;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class AlquilerSeeder extends Seeder
{
    public function run(): void
    {
        $alquileres = [
            ['propiedad' => 'Calle Gran Vía 1', 'inquilino' => 'inquilino1@example.com', 'estado' => 'aprobado', 'fecha_inicio' => '2024-01-15'],
            ['propiedad' => 'Calle Alcalá 45', 'inquilino' => 'inquilino2@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-12-01'],
            ['propiedad' => 'Paseo de Gracia 100', 'inquilino' => 'inquilino3@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-11-20'],
            ['propiedad' => 'Calle Còrsega 50', 'inquilino' => 'inquilino4@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-10-15'],
            ['propiedad' => 'Avenida Malvarrosa 30', 'inquilino' => 'inquilino5@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-09-01'],
            ['propiedad' => 'Calle Serrería 20', 'inquilino' => 'inquilino6@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-08-10'],
            ['propiedad' => 'Calle Betis 60', 'inquilino' => 'inquilino7@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-07-25'],
            ['propiedad' => 'Avenida Constitución 15', 'inquilino' => 'inquilino8@example.com', 'estado' => 'aprobado', 'fecha_inicio' => '2024-02-01'],
            ['propiedad' => 'Calle Ercilla 5', 'inquilino' => 'inquilino1@example.com', 'estado' => 'pendiente', 'fecha_inicio' => '2024-03-01'],
            ['propiedad' => 'Plaza Zabalburu 8', 'inquilino' => 'inquilino2@example.com', 'estado' => 'cancelado', 'fecha_inicio' => '2023-06-15'],
            ['propiedad' => 'Avenida de América 10', 'inquilino' => 'inquilino3@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-05-20'],
            ['propiedad' => 'Calle Muntaner 35', 'inquilino' => 'inquilino4@example.com', 'estado' => 'finalizado', 'fecha_inicio' => '2022-12-01'],
            ['propiedad' => 'Calle Colón 70', 'inquilino' => 'inquilino5@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-04-10'],
            ['propiedad' => 'Avenida Lehendakari Aguirre 60', 'inquilino' => 'inquilino6@example.com', 'estado' => 'aprobado', 'fecha_inicio' => '2024-01-01'],
            ['propiedad' => 'Avenida de la Buhaira 12', 'inquilino' => 'inquilino7@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-03-15'],
            ['propiedad' => 'Calle Gran Vía 1', 'inquilino' => 'inquilino8@example.com', 'estado' => 'pendiente', 'fecha_inicio' => '2024-02-15'],
            ['propiedad' => 'Calle Alcalá 45', 'inquilino' => 'inquilino1@example.com', 'estado' => 'cancelado', 'fecha_inicio' => '2023-02-10'],
            ['propiedad' => 'Paseo de Gracia 100', 'inquilino' => 'inquilino2@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-01-30'],
            ['propiedad' => 'Calle Còrsega 50', 'inquilino' => 'inquilino3@example.com', 'estado' => 'finalizado', 'fecha_inicio' => '2022-11-15'],
            ['propiedad' => 'Avenida Malvarrosa 30', 'inquilino' => 'inquilino4@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-02-28'],
        ];

        foreach ($alquileres as $data) {
            $propiedad = Propiedad::where('direccion_propiedad', $data['propiedad'])->first();
            $inquilino = Usuario::where('email_usuario', $data['inquilino'])->first();
            
            $admins = Usuario::whereHas('roles', function ($q) {
                $q->where('nombre_rol', 'admin');
            })->get();
            $admin = $admins->isNotEmpty() ? $admins->random() : null;

            if ($propiedad && $inquilino) {
                Alquiler::firstOrCreate(
                    ['id_propiedad_fk' => $propiedad->id_propiedad, 'id_inquilino_fk' => $inquilino->id_usuario],
                    [
                        'estado_alquiler' => $data['estado'],
                        'fecha_inicio_alquiler' => $data['fecha_inicio'],
                        'fecha_fin_alquiler' => $data['estado'] === 'finalizado' ? now()->subDays(30) : null,
                        'id_admin_aprueba_fk' => $admin?->id_usuario,
                        'aprobado_alquiler' => $data['estado'] !== 'pendiente' ? now()->subDays(5) : null,
                        'creado_alquiler' => now(),
                        'actualizado_alquiler' => now(),
                    ]
                );
            }
        }
    }
}