<?php

namespace Database\Seeders;

use App\Models\Propiedad;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PropiedadSeeder extends Seeder
{
    public function run(): void
    {
        $ciudades = ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Bilbao', 'M├ílaga'];

        $arrendadores = [
            ['email' => 'jlavignole@spotstay.com', 'nombre' => 'Jaume Lavignole'],
            ['email' => 'ivazquez@spotstay.com', 'nombre' => 'Isabel V├ízquez'],
            ['email' => 'eruiz@spotstay.com', 'nombre' => 'Enrique Ruiz'],
            ['email' => 'mgarcia@spotstay.com', 'nombre' => 'Mar├¡a Garc├¡a'],
            ['email' => 'jjimenez@spotstay.com', 'nombre' => 'Jorge Jim├®nez'],
            ['email' => 'pnunez@spotstay.com', 'nombre' => 'Patricia N├║├▒ez'],
            ['email' => 'amoreno@spotstay.com', 'nombre' => 'Alejandro Moreno'],
            ['email' => 'evargas@spotstay.com', 'nombre' => 'Elena Vargas'],
            ['email' => 'snavarro@spotstay.com', 'nombre' => 'Sergio Navarro'],
            ['email' => 'gcampos@spotstay.com', 'nombre' => 'Gloria Campos'],
            ['email' => 'riglesias@spotstay.com', 'nombre' => 'Rafael Iglesias'],
            ['email' => 'cmolina@spotstay.com', 'nombre' => 'Catalina Molina'],
            ['email' => 'ivega@spotstay.com', 'nombre' => 'Iago Vega'],
            ['email' => 'lherrera@spotstay.com', 'nombre' => 'Lorena Herrera'],
            ['email' => 'vgutierrez@spotstay.com', 'nombre' => 'V├¡ctor Gutierrez'],
            ['email' => 'sramos@spotstay.com', 'nombre' => 'Sandra Ramos'],
            ['email' => 'lflores@spotstay.com', 'nombre' => 'Lucas Flores'],
            ['email' => 'vcabrera@spotstay.com', 'nombre' => 'Valeria Cabrera'],
            ['email' => 'mramirez@spotstay.com', 'nombre' => 'Mart├¡n Ram├¡rez'],
            ['email' => 'scortes@spotstay.com', 'nombre' => 'Sof├¡a Cort├®s'],
            ['email' => 'asoto@spotstay.com', 'nombre' => 'Andr├®s Soto'],
            ['email' => 'ddelgado@spotstay.com', 'nombre' => 'Daniela Delgado'],
            ['email' => 'cparra@spotstay.com', 'nombre' => 'Cristian Parra'],
            ['email' => 'ncastro@spotstay.com', 'nombre' => 'Natalia Castro'],
            ['email' => 'grojas@spotstay.com', 'nombre' => 'Guillermo Rojas'],
        ];

        $gestores = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'gestor');
        })->get();

        if ($gestores->isEmpty()) {
            return;
        }

        $propiedadesData = [];
        $counter = 0;

        // Generar propiedades para cada arrendador
        foreach ($arrendadores as $arrendadorData) {
            $arrendador = Usuario::where('email_usuario', $arrendadorData['email'])->first();
            
            if (!$arrendador) {
                continue;
            }

            // Cada arrendador tiene m├¡nimo 2 propiedades
            // Primera: estado 'borrador'
            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestores->get($counter % $gestores->count())->id_usuario,
                'titulo' => $this->generarTitulo($counter),
                'calle' => $this->generarCalle($counter),
                'numero' => rand(1, 999),
                'piso' => rand(0, 6),
                'puerta' => chr(65 + rand(0, 4)), // A-E
                'ciudad' => $ciudades[$counter % count($ciudades)],
                'cp' => $this->generarCP($ciudades[$counter % count($ciudades)]),
                'lat' => $this->generarLatitud($ciudades[$counter % count($ciudades)]),
                'lng' => $this->generarLongitud($ciudades[$counter % count($ciudades)]),
                'descripcion' => 'Piso completamente equipado en zona c├®ntrica con acceso a transporte p├║blico.',
                'precio' => rand(60, 250) * 10, // 600-2500
                'gastos' => json_encode($this->generarGastos()),
                'estado' => 'borrador',
                'creado' => now()->subDays(rand(30, 90)),
            ];

            // Segunda: estado 'alquilada' o 'publicada'
            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestores->get(($counter + 1) % $gestores->count())->id_usuario,
                'titulo' => $this->generarTitulo($counter + 1),
                'calle' => $this->generarCalle($counter + 1),
                'numero' => rand(1, 999),
                'piso' => rand(0, 6),
                'puerta' => chr(65 + rand(0, 4)),
                'ciudad' => $ciudades[($counter + 1) % count($ciudades)],
                'cp' => $this->generarCP($ciudades[($counter + 1) % count($ciudades)]),
                'lat' => $this->generarLatitud($ciudades[($counter + 1) % count($ciudades)]),
                'lng' => $this->generarLongitud($ciudades[($counter + 1) % count($ciudades)]),
                'descripcion' => 'Apartamento moderno con todas las comodidades en pleno centro urbano.',
                'precio' => rand(60, 250) * 10,
                'gastos' => json_encode($this->generarGastos()),
                'estado' => $counter % 2 === 0 ? 'alquilada' : 'publicada',
                'creado' => now()->subDays(rand(5, 60)),
            ];

            // Tercera propiedad (algunos arrendadores): compartida con m├║ltiples inquilinos
            if ($counter % 3 === 0) {
                $propiedadesData[] = [
                    'arrendador_id' => $arrendador->id_usuario,
                    'gestor_id' => $gestores->get(($counter + 2) % $gestores->count())->id_usuario,
                    'titulo' => $this->generarTitulo($counter + 2),
                    'calle' => $this->generarCalle($counter + 2),
                    'numero' => rand(1, 999),
                    'piso' => rand(0, 6),
                    'puerta' => chr(65 + rand(0, 4)),
                    'ciudad' => $ciudades[($counter + 2) % count($ciudades)],
                    'cp' => $this->generarCP($ciudades[($counter + 2) % count($ciudades)]),
                    'lat' => $this->generarLatitud($ciudades[($counter + 2) % count($ciudades)]),
                    'lng' => $this->generarLongitud($ciudades[($counter + 2) % count($ciudades)]),
                    'descripcion' => 'Vivienda amplia compartida, ideal para grupos de amigos o compa├▒eros de trabajo.',
                    'precio' => rand(60, 180) * 10,
                    'gastos' => json_encode($this->generarGastos()),
                    'estado' => 'alquilada',
                    'creado' => now()->subDays(rand(10, 120)),
                ];
            }

            $counter += 3;
        }

        // Insertar propiedades
        foreach ($propiedadesData as $data) {
            Propiedad::firstOrCreate(
                [
                    'titulo_propiedad' => $data['titulo'],
                    'calle_propiedad' => $data['calle'],
                    'numero_propiedad' => $data['numero'],
                ],
                [
                    'id_arrendador_fk' => $data['arrendador_id'],
                    'id_gestor_fk' => $data['gestor_id'],
                    'piso_propiedad' => $data['piso'],
                    'puerta_propiedad' => $data['puerta'],
                    'ciudad_propiedad' => $data['ciudad'],
                    'codigo_postal_propiedad' => $data['cp'],
                    'latitud_propiedad' => $data['lat'],
                    'longitud_propiedad' => $data['lng'],
                    'descripcion_propiedad' => $data['descripcion'],
                    'precio_propiedad' => $data['precio'],
                    'gastos_propiedad' => $data['gastos'],
                    'estado_propiedad' => $data['estado'],
                    'creado_propiedad' => $data['creado'],
                    'actualizado_propiedad' => $data['creado'],
                ]
            );
        }
    }

    private function generarTitulo(int $index): string
    {
        $titulos = [
            'Piso luminoso en el centro',
            'Apartamento con terraza',
            'Estudio moderno y acogedor',
            'Casa colonial reformada',
            'Loft con altos techos',
            'Vivienda acogedora con jard├¡n',
            'Piso c├®ntrico cerca de transporte',
            'Apartamento con vistas',
            'Estudio funcional y pr├íctico',
            'Casa tradicional restaurada',
        ];
        return $titulos[$index % count($titulos)] . ' ' . ($index + 1);
    }

    private function generarCalle(int $index): string
    {
        $calles = [
            'Calle Mayor', 'Avenida de la Paz', 'Calle Pr├¡ncipe', 'Paseo del Prado',
            'Calle Alcal├í', 'Gran V├¡a', 'Calle Serrano', 'Avenida Paseo de Gracia',
            'Calle Ramblas', 'Avenida Diagonal', 'Calle Col├│n', 'Paseo de la Costa',
            'Calle del Carmen', 'Avenida de la Libertad', 'Calle Nueva',
        ];
        return $calles[$index % count($calles)];
    }

    private function generarCP(string $ciudad): string
    {
        $cps = [
            'Madrid' => ['28001', '28002', '28003', '28004', '28005'],
            'Barcelona' => ['08001', '08002', '08003', '08004', '08008'],
            'Valencia' => ['46001', '46002', '46003', '46004'],
            'Sevilla' => ['41001', '41002', '41003', '41004'],
            'Bilbao' => ['48001', '48002', '48003', '48004'],
            'M├ílaga' => ['29001', '29002', '29005', '29007'],
        ];
        $codigos = $cps[$ciudad] ?? ['00000'];
        return $codigos[array_rand($codigos)];
    }

    private function generarLatitud(string $ciudad): float
    {
        $lats = [
            'Madrid' => 40.4168,
            'Barcelona' => 41.3874,
            'Valencia' => 39.4699,
            'Sevilla' => 37.3891,
            'Bilbao' => 43.2630,
            'M├ílaga' => 36.7213,
        ];
        return $lats[$ciudad] + (rand(-100, 100) / 1000);
    }

    private function generarLongitud(string $ciudad): float
    {
        $lngs = [
            'Madrid' => -3.7038,
            'Barcelona' => 2.1686,
            'Valencia' => -0.3761,
            'Sevilla' => -5.9845,
            'Bilbao' => -2.9350,
            'M├ílaga' => -4.4214,
        ];
        return $lngs[$ciudad] + (rand(-100, 100) / 1000);
    }

    private function generarGastos(): array
    {
        $gastos = [
            'agua' => rand(20, 40),
            'luz' => rand(40, 80),
            'comunidad' => rand(30, 70),
        ];
        
        // Algunos incluyen gas (30% de probabilidad)
        if (rand(0, 2) === 0) {
            $gastos['gas'] = rand(20, 50);
        }

        return $gastos;
    }
}

