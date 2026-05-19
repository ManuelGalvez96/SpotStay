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

        $mGestor = $gestores->firstWhere('email_usuario', 'mgestor@spotstay.com');
        $otrosGestores = $gestores->filter(function ($g) {
            return $g->email_usuario !== 'mgestor@spotstay.com';
        })->values();

        $this->crearPropiedadesParaArrendadores($arrendadores, $mGestor, $otrosGestores, $ciudades);
    }

    private function crearPropiedadesParaArrendadores(
        array $arrendadores,
        ?Usuario $mGestor,
        $otrosGestores,
        array $ciudades
    ): void {
        $propiedadesData = [];
        $counter = 0;
        $estados = ['publicada', 'alquilada', 'borrador', 'publicada', 'alquilada'];

        foreach ($arrendadores as $i => $arrendadorData) {
            $arrendador = Usuario::where('email_usuario', $arrendadorData['email'])->first();
            if (!$arrendador) {
                continue;
            }

            $esPar = $i % 2 === 0;

            if ($mGestor && ($i < 10 || $esPar)) {
                $gestor = $mGestor;
            } else {
                $gestor = $otrosGestores->get($i % max($otrosGestores->count(), 1));
            }

            $ciudad1 = $ciudades[$counter % count($ciudades)];
            $estado1 = $mGestor && $gestor->id_usuario === $mGestor->id_usuario && $i < 8
                ? $estados[$i % count($estados)]
                : 'borrador';

            $baños1 = rand(1, 3);

            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestor->id_usuario,
                'titulo' => $this->generarTitulo($counter),
                'calle' => $this->generarCalle($counter),
                'numero' => 100 + $counter,
                'piso' => rand(0, 5),
                'puerta' => chr(65 + rand(0, 4)),
                'ciudad' => $ciudad1,
                'cp' => $this->generarCP($ciudad1),
                'lat' => $this->generarLatitud($ciudad1),
                'lng' => $this->generarLongitud($ciudad1),
                'descripcion' => 'Piso completamente equipado en zona céntrica con acceso a transporte público.',
                'precio' => rand(60, 250) * 10,
                'tipo' => $this->generarTipo($counter),
                'habitaciones' => rand(1, 4),
                'banos' => $baños1,
                'metros' => rand(45, 120),
                'estado' => $estado1,
                'amueblado' => (bool) rand(0, 1),
                'ascensor' => (bool) rand(0, 1),
                'piscina' => (bool) rand(0, 1),
                'terraza' => (bool) rand(0, 1),
                'garaje' => (bool) rand(0, 1),
                'aire_acondicionado' => (bool) rand(0, 1),
                'calefaccion' => (bool) rand(0, 1),
                'trastero' => (bool) rand(0, 1),
                'adicional' => rand(0, 3) === 0 ? 'Trastero incluido. Zona tranquila.' : null,
                'creado' => now()->subDays(rand(30, 90)),
            ];

            $gestor2 = $mGestor && ($i < 8 || ($i >= 10 && !$esPar))
                ? $mGestor
                : $otrosGestores->get(($i + 1) % max($otrosGestores->count(), 1));

            $ciudad2 = $ciudades[($counter + 1) % count($ciudades)];
            $estado2 = $mGestor && $gestor2->id_usuario === $mGestor->id_usuario && $i < 6
                ? ($i % 2 === 0 ? 'alquilada' : 'publicada')
                : ($counter % 2 === 0 ? 'alquilada' : 'publicada');

            $baños2 = rand(1, 3);

            $propiedadesData[] = [
                'arrendador_id' => $arrendador->id_usuario,
                'gestor_id' => $gestor2->id_usuario,
                'titulo' => $this->generarTitulo($counter + 1),
                'calle' => $this->generarCalle($counter + 1),
                'numero' => 200 + $counter,
                'piso' => rand(0, 5),
                'puerta' => chr(65 + rand(0, 4)),
                'ciudad' => $ciudad2,
                'cp' => $this->generarCP($ciudad2),
                'lat' => $this->generarLatitud($ciudad2),
                'lng' => $this->generarLongitud($ciudad2),
                'descripcion' => 'Apartamento moderno con todas las comodidades en pleno centro urbano.',
                'precio' => rand(60, 250) * 10,
                'tipo' => $this->generarTipo($counter + 1),
                'habitaciones' => rand(2, 5),
                'banos' => $baños2,
                'metros' => rand(60, 180),
                'estado' => $estado2,
                'amueblado' => (bool) rand(0, 1),
                'ascensor' => true,
                'piscina' => $i % 3 === 0,
                'terraza' => $i % 2 === 0,
                'garaje' => (bool) rand(0, 1),
                'aire_acondicionado' => true,
                'calefaccion' => true,
                'trastero' => $i % 4 === 0,
                'adicional' => rand(0, 2) === 0 ? 'Aire acondicionado y calefacción central.' : null,
                'creado' => now()->subDays(rand(5, 60)),
            ];

            if ($counter % 3 === 0) {
                $gestor3 = $mGestor && $i < 5
                    ? $mGestor
                    : $otrosGestores->get(($i + 2) % max($otrosGestores->count(), 1));

                $ciudad3 = $ciudades[($counter + 2) % count($ciudades)];
                $baños3 = rand(1, 4);

                $propiedadesData[] = [
                    'arrendador_id' => $arrendador->id_usuario,
                    'gestor_id' => $gestor3->id_usuario,
                    'titulo' => $this->generarTitulo($counter + 2) . ' Premium',
                    'calle' => $this->generarCalle($counter + 2),
                    'numero' => 300 + $counter,
                    'piso' => rand(0, 4),
                    'puerta' => chr(65 + rand(0, 4)),
                    'ciudad' => $ciudad3,
                    'cp' => $this->generarCP($ciudad3),
                    'lat' => $this->generarLatitud($ciudad3),
                    'lng' => $this->generarLongitud($ciudad3),
                    'descripcion' => 'Vivienda amplia compartida, ideal para grupos de amigos o compañeros de trabajo.',
                    'precio' => rand(60, 180) * 10,
                    'tipo' => 'Casa',
                    'habitaciones' => rand(4, 6),
                    'banos' => $baños3,
                    'metros' => rand(150, 250),
                    'estado' => 'alquilada',
                    'amueblado' => true,
                    'ascensor' => false,
                    'piscina' => true,
                    'terraza' => true,
                    'garaje' => true,
                    'aire_acondicionado' => true,
                    'calefaccion' => true,
                    'trastero' => true,
                    'adicional' => 'Jardín privado y piscina comunitaria.',
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
                    'numero_propiedad' => (string) $data['numero'],
                ],
                [
                    'id_arrendador_fk' => $data['arrendador_id'],
                    'id_gestor_fk' => $data['gestor_id'],
                    'piso_propiedad' => (string) $data['piso'],
                    'puerta_propiedad' => $data['puerta'],
                    'ciudad_propiedad' => $data['ciudad'],
                    'codigo_postal_propiedad' => $data['cp'],
                    'latitud_propiedad' => $data['lat'],
                    'longitud_propiedad' => $data['lng'],
                    'descripcion_propiedad' => $data['descripcion'],
                    'precio_propiedad' => $data['precio'],
                    'tipo_propiedad' => $data['tipo'],
                    'habitaciones_propiedad' => (string) $data['habitaciones'],
                    'banos_propiedad' => $data['banos'],
                    'metros_cuadrados_propiedad' => $data['metros'],
                    'estado_propiedad' => $data['estado'],
                    'amueblado_propiedad' => $data['amueblado'],
                    'ascensor_propiedad' => $data['ascensor'],
                    'piscina_propiedad' => $data['piscina'],
                    'terraza_propiedad' => $data['terraza'],
                    'garaje_propiedad' => $data['garaje'],
                    'aire_acondicionado_propiedad' => $data['aire_acondicionado'],
                    'calefaccion_propiedad' => $data['calefaccion'],
                    'trastero_propiedad' => $data['trastero'],
                    'adicional_propiedad' => $data['adicional'],
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
