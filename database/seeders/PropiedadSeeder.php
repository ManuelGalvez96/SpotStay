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
        $ciudades = [
            'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Bilbao', 'Malaga',
            'Zaragoza', 'Alicante', 'Murcia', 'Palma', 'Las Palmas', 'Valladolid',
            'Vigo', 'Gijon', 'Granada', 'San Sebastian'
        ];

        $arrendadores = [
            ['email' => 'jlavignole@spotstay.com', 'nombre' => 'Jaume Lavignole'],
            ['email' => 'ivazquez@spotstay.com', 'nombre' => 'Isabel Vázquez'],
            ['email' => 'eruiz@spotstay.com', 'nombre' => 'Enrique Ruiz'],
            ['email' => 'mgarcia@spotstay.com', 'nombre' => 'María García'],
            ['email' => 'jjimenez@spotstay.com', 'nombre' => 'Jorge Jiménez'],
            ['email' => 'pnunez@spotstay.com', 'nombre' => 'Patricia Núñez'],
            ['email' => 'amoreno@spotstay.com', 'nombre' => 'Alejandro Moreno'],
            ['email' => 'evargas@spotstay.com', 'nombre' => 'Elena Vargas'],
            ['email' => 'snavarro@spotstay.com', 'nombre' => 'Sergio Navarro'],
            ['email' => 'gcampos@spotstay.com', 'nombre' => 'Gloria Campos'],
            ['email' => 'riglesias@spotstay.com', 'nombre' => 'Rafael Iglesias'],
            ['email' => 'cmolina@spotstay.com', 'nombre' => 'Catalina Molina'],
            ['email' => 'ivega@spotstay.com', 'nombre' => 'Iago Vega'],
            ['email' => 'lherrera@spotstay.com', 'nombre' => 'Lorena Herrera'],
            ['email' => 'vgutierrez@spotstay.com', 'nombre' => 'Víctor Gutiérrez'],
            ['email' => 'sramos@spotstay.com', 'nombre' => 'Sandra Ramos'],
            ['email' => 'lflores@spotstay.com', 'nombre' => 'Lucas Flores'],
            ['email' => 'vcabrera@spotstay.com', 'nombre' => 'Valeria Cabrera'],
            ['email' => 'mramirez@spotstay.com', 'nombre' => 'Martín Ramírez'],
            ['email' => 'scortes@spotstay.com', 'nombre' => 'Sofía Cortés'],
            ['email' => 'asoto@spotstay.com', 'nombre' => 'Andrés Soto'],
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

        foreach ($arrendadores as $arrendadorData) {
            $arrendador = Usuario::where('email_usuario', $arrendadorData['email'])->first();

            if (!$arrendador) {
                continue;
            }

            $gestor = $gestores->get($counter % $gestores->count());

            // Primera: estado 'borrador'
            $ciudad1 = $ciudades[$counter % count($ciudades)];
            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestor->id_usuario,
                'titulo' => $this->generarTitulo($counter),
                'calle' => $this->generarCalle($counter),
                'numero' => rand(1, 999),
                'piso' => rand(0, 6),
                'puerta' => chr(65 + rand(0, 4)),
                'ciudad' => $ciudad1,
                'cp' => $this->generarCP($ciudad1),
                'lat' => $this->generarLatitud($ciudad1),
                'lng' => $this->generarLongitud($ciudad1),
                'descripcion' => 'Piso completamente equipado en zona céntrica con acceso a transporte público.',
                'precio' => rand(60, 250) * 10,
                'tipo' => $this->generarTipo($counter),
                'habitaciones' => rand(1, 4),
                'metros' => rand(45, 120),
                'estado' => 'borrador',
                'creado' => now()->subDays(rand(30, 90)),
            ];

            // Segunda: estado 'alquilada' o 'publicada'
            $gestor2 = $gestores->get(($counter + 1) % $gestores->count());
            $ciudad2 = $ciudades[($counter + 1) % count($ciudades)];
            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestor2->id_usuario,
                'titulo' => $this->generarTitulo($counter + 1),
                'calle' => $this->generarCalle($counter + 1),
                'numero' => rand(1, 999),
                'piso' => rand(0, 6),
                'puerta' => chr(65 + rand(0, 4)),
                'ciudad' => $ciudad2,
                'cp' => $this->generarCP($ciudad2),
                'lat' => $this->generarLatitud($ciudad2),
                'lng' => $this->generarLongitud($ciudad2),
                'descripcion' => 'Apartamento moderno con todas las comodidades en pleno centro urbano.',
                'precio' => rand(60, 250) * 10,
                'tipo' => $this->generarTipo($counter + 1),
                'habitaciones' => rand(2, 5),
                'metros' => rand(60, 180),
                'estado' => $counter % 2 === 0 ? 'alquilada' : 'publicada',
                'creado' => now()->subDays(rand(5, 60)),
            ];

            // Tercera propiedad
            if ($counter % 3 === 0) {
                $gestor3 = $gestores->get(($counter + 2) % $gestores->count());
                $ciudad3 = $ciudades[($counter + 2) % count($ciudades)];
                $propiedadesData[] = [
                    'arrendador_id' => $arrendador->id_usuario,
                    'gestor_id' => $gestor3->id_usuario,
                    'titulo' => $this->generarTitulo($counter + 2) . ' Premium',
                    'calle' => $this->generarCalle($counter + 2),
                    'numero' => rand(1, 999),
                    'piso' => rand(0, 6),
                    'puerta' => chr(65 + rand(0, 4)),
                    'ciudad' => $ciudad3,
                    'cp' => $this->generarCP($ciudad3),
                    'lat' => $this->generarLatitud($ciudad3),
                    'lng' => $this->generarLongitud($ciudad3),
                    'descripcion' => 'Vivienda amplia compartida, ideal para grupos de amigos o compañeros de trabajo.',
                    'precio' => rand(60, 180) * 10,
                    'tipo' => 'Casa',
                    'habitaciones' => rand(4, 6),
                    'metros' => rand(150, 250),
                    'estado' => 'alquilada',
                    'creado' => now()->subDays(rand(10, 120)),
                ];
            }

            $counter += 3;
        }

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
                    'tipo_propiedad' => $data['tipo'],
                    'habitaciones_propiedad' => $data['habitaciones'],
                    'metros_cuadrados_propiedad' => $data['metros'],
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
            'Vivienda acogedora con jardín',
            'Piso céntrico cerca de transporte',
            'Apartamento con vistas',
            'Estudio funcional y práctico',
            'Casa tradicional restaurada',
        ];
        return $titulos[$index % count($titulos)] . ' ' . ($index + 1);
    }

    private function generarCalle(int $index): string
    {
        $calles = [
            'Calle Mayor', 'Avenida de la Paz', 'Calle Príncipe', 'Paseo del Prado',
            'Calle Alcalá', 'Gran Vía', 'Calle Serrano', 'Avenida Paseo de Gracia',
            'Calle Ramblas', 'Avenida Diagonal', 'Calle Colón', 'Paseo de la Costa',
            'Calle del Carmen', 'Avenida de la Libertad', 'Calle Nueva', 'Calle Real'
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
            'Malaga' => ['29001', '29002', '29005', '29007'],
            'Zaragoza' => ['50001', '50002', '50003'],
            'Alicante' => ['03001', '03002', '03003'],
            'Murcia' => ['30001', '30002', '30003'],
            'Palma' => ['07001', '07002', '07003'],
            'Las Palmas' => ['35001', '35002', '35003'],
            'Valladolid' => ['47001', '47002', '47003'],
            'Vigo' => ['36201', '36202', '36203'],
            'Gijon' => ['33201', '33202', '33203'],
            'Granada' => ['18001', '18002', '18003'],
            'San Sebastian' => ['20001', '20002', '20003']
        ];
        $codigos = $cps[$ciudad] ?? ['00000'];
        return $codigos[array_rand($codigos)];
    }

    private function generarLatitud(string $ciudad): float
    {
        $lats = [
            'Madrid' => 40.4168, 'Barcelona' => 41.3874, 'Valencia' => 39.4699,
            'Sevilla' => 37.3891, 'Bilbao' => 43.2630, 'Malaga' => 36.7213,
            'Zaragoza' => 41.6488, 'Alicante' => 38.3452, 'Murcia' => 37.9922,
            'Palma' => 39.5693, 'Las Palmas' => 28.1235, 'Valladolid' => 41.6523,
            'Vigo' => 42.2406, 'Gijon' => 43.5357, 'Granada' => 37.1773,
            'San Sebastian' => 43.3183
        ];
        return $lats[$ciudad] + (rand(-100, 100) / 1000);
    }

    private function generarLongitud(string $ciudad): float
    {
        $lngs = [
            'Madrid' => -3.7038, 'Barcelona' => 2.1686, 'Valencia' => -0.3761,
            'Sevilla' => -5.9845, 'Bilbao' => -2.9350, 'Malaga' => -4.4214,
            'Zaragoza' => -0.8891, 'Alicante' => -0.4810, 'Murcia' => -1.1307,
            'Palma' => 2.6502, 'Las Palmas' => -15.4363, 'Valladolid' => -4.7245,
            'Vigo' => -8.7207, 'Gijon' => -5.6615, 'Granada' => -3.5986,
            'San Sebastian' => -1.9812
        ];
        return $lngs[$ciudad] + (rand(-100, 100) / 1000);
    }

    private function generarTipo(int $index): string
    {
        $tipos = ['Piso', 'Apartamento', 'Estudio', 'Ático', 'Loft', 'Dúplex'];
        return $tipos[$index % count($tipos)];
    }
}
