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
            // Alquiler de prueba para Gestión: María García (inquilino@spotstay.com) → 4 propiedades en 4 ciudades distintas
            ['propiedad' => 'Calle Mayor 14',        'inquilino' => 'inquilino@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-01-01'], // Madrid
            ['propiedad' => 'Av. Diagonal 88',        'inquilino' => 'inquilino@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-02-01', 'fecha_fin' => '2026-05-01'], // Barcelona (contrato finaliza 01/05/2026)
            ['propiedad' => 'Alameda de Hércules 3', 'inquilino' => 'inquilino@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-03-01'], // Sevilla
            ['propiedad' => 'Calle Colón 8',          'inquilino' => 'inquilino@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-04-01'], // Valencia
            ['propiedad' => 'Calle Mayor 14', 'inquilino' => 'laura@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-01-15'],
            ['propiedad' => 'Calle Serrano 47', 'inquilino' => 'pedro@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-02-01'],
            ['propiedad' => 'Av. Diagonal 88', 'inquilino' => 'sofia@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2025-01-20'],
            ['propiedad' => 'Calle Pelai 12', 'inquilino' => 'carmen.iglesias@email.com', 'estado' => 'activo', 'fecha_inicio' => '2025-03-01'],
            ['propiedad' => 'Calle Mayor 14', 'inquilino' => 'andres.molina@email.com', 'estado' => 'activo', 'fecha_inicio' => '2025-04-01'],
            ['propiedad' => 'Calle Fuencarral 22', 'inquilino' => 'patricia.vega@email.com', 'estado' => 'activo', 'fecha_inicio' => '2025-05-01'],
            ['propiedad' => 'Paseo de Gracia 5', 'inquilino' => 'javier.moya@email.com', 'estado' => 'activo', 'fecha_inicio' => '2025-05-15'],
            ['propiedad' => 'Alameda de Hércules 3', 'inquilino' => 'lucia.serrano@email.com', 'estado' => 'activo', 'fecha_inicio' => '2025-06-01'],
            
            // Alquileres de David
            ['propiedad' => 'Calle Mayor 14', 'inquilino' => 'inquilino1@example.com', 'estado' => 'activo', 'fecha_inicio' => '2024-01-15'],
            ['propiedad' => 'Calle Serrano 47', 'inquilino' => 'inquilino2@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-12-01'],
            ['propiedad' => 'Calle Fuencarral 22', 'inquilino' => 'inquilino3@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-11-20'],
            ['propiedad' => 'Av. Diagonal 88', 'inquilino' => 'inquilino4@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-10-15'],
            ['propiedad' => 'Calle Pelai 12', 'inquilino' => 'inquilino5@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-09-01'],
            ['propiedad' => 'Paseo de Gracia 5', 'inquilino' => 'inquilino6@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-08-10'],
            ['propiedad' => 'Calle Larios 7', 'inquilino' => 'inquilino7@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-07-25'],
            ['propiedad' => 'Alameda de Hércules 3', 'inquilino' => 'inquilino8@example.com', 'estado' => 'activo', 'fecha_inicio' => '2024-02-01'],
            ['propiedad' => 'Calle Colón 8', 'inquilino' => 'inquilino1@example.com', 'estado' => 'pendiente', 'fecha_inicio' => '2024-03-01'],
            ['propiedad' => 'Gran Vía 45', 'inquilino' => 'inquilino2@example.com', 'estado' => 'cancelado', 'fecha_inicio' => '2023-06-15'],
            ['propiedad' => 'Calle Coso 15', 'inquilino' => 'inquilino3@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-05-20'],
            ['propiedad' => 'Paseo de la Explanada 3', 'inquilino' => 'inquilino4@example.com', 'estado' => 'finalizado', 'fecha_inicio' => '2022-12-01'],
            ['propiedad' => 'Calle Reyes Católicos 12', 'inquilino' => 'inquilino5@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-04-10'],
            ['propiedad' => 'Plaza de las Flores 7', 'inquilino' => 'inquilino6@example.com', 'estado' => 'aprobado', 'fecha_inicio' => '2024-01-01'],
            ['propiedad' => 'Calle Miguel Íscar 15', 'inquilino' => 'inquilino7@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-03-15'],
            ['propiedad' => 'Calle Mayor 14', 'inquilino' => 'inquilino8@example.com', 'estado' => 'pendiente', 'fecha_inicio' => '2024-02-15'],
            ['propiedad' => 'Calle Serrano 47', 'inquilino' => 'inquilino1@example.com', 'estado' => 'cancelado', 'fecha_inicio' => '2023-02-10'],
            ['propiedad' => 'Calle Fuencarral 22', 'inquilino' => 'inquilino2@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-01-30'],
            ['propiedad' => 'Av. Diagonal 88', 'inquilino' => 'inquilino3@example.com', 'estado' => 'finalizado', 'fecha_inicio' => '2022-11-15'],
            ['propiedad' => 'Calle Pelai 12', 'inquilino' => 'inquilino4@example.com', 'estado' => 'activo', 'fecha_inicio' => '2023-02-28'],
            
            // Nuevos alquileres para cubrir requerimientos del seeder (todos inquilinos activos y un arrendador pagando)
            ['propiedad' => 'Calle Larios 7', 'inquilino' => 'test1@example.com', 'estado' => 'activo', 'fecha_inicio' => '2024-05-01'],
            ['propiedad' => 'Paseo de la Explanada 3', 'inquilino' => 'elena@spotstay.com', 'estado' => 'activo', 'fecha_inicio' => '2024-04-10'], // elena es arrendadora
            ['propiedad' => 'Calle Miguel Íscar 15', 'inquilino' => 'test2@example.com', 'estado' => 'activo', 'fecha_inicio' => '2024-06-01'], // test2 es arrendador
        ];

        $propiedades = Propiedad::all();
        $usuarios = Usuario::all();
        $admins = Usuario::whereHas('roles', function ($q) {
            $q->where('nombre_rol', 'admin');
        })->get();

        if ($propiedades->isEmpty() || $usuarios->isEmpty()) {
            return;
        }

        foreach ($alquileres as $data) {
            $propiedad = $propiedades->first(function ($item) use ($data) {
                $direccion = trim(implode(' ', array_filter([
                    $item->calle_propiedad,
                    $item->numero_propiedad,
                ])));

                return $direccion === $data['propiedad'];
            });
            $inquilino = $usuarios->firstWhere('email_usuario', $data['inquilino']);
            $admin = $admins->isNotEmpty() ? $admins->random() : null;

            if ($propiedad && $inquilino) {
                Alquiler::firstOrCreate(
                    ['id_propiedad_fk' => $propiedad->id_propiedad, 'id_inquilino_fk' => $inquilino->id_usuario],
                    [
                        'estado_alquiler' => $data['estado'],
                        'fecha_inicio_alquiler' => $data['fecha_inicio'],
                        'fecha_fin_alquiler' => $data['fecha_fin'] ?? ($data['estado'] === 'finalizado' ? now()->subDays(30)->format('Y-m-d') : null),
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