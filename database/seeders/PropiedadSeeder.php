<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropiedadSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = [
            // Arrendador 1
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso en Calle Mayor',
                'direccion' => 'Calle Mayor 14',
                'ciudad' => 'Madrid',
                'cp' => '28001',
                'lat' => 40.4153,
                'lng' => -3.7074,
                'descripcion' => 'Amplio piso en el centro de Madrid',
                'precio' => 1200.00,
                'gastos' => json_encode(['agua' => 30, 'luz' => 50, 'comunidad' => 40, 'gas' => 25]),
                'estado' => 'alquilada',
            ],
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Calle Serrano',
                'direccion' => 'Calle Serrano 47',
                'ciudad' => 'Madrid',
                'cp' => '28001',
                'lat' => 40.4307,
                'lng' => -3.6869,
                'descripcion' => 'Piso elegante en zona de Serrano',
                'precio' => 1800.00,
                'gastos' => json_encode(['agua' => 35, 'luz' => 60, 'comunidad' => 50]),
                'estado' => 'alquilada',
            ],
            [
                'arrendador_email' => 'inquilino@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Estudio Fuencarral',
                'direccion' => 'Calle Fuencarral 22',
                'ciudad' => 'Madrid',
                'cp' => '28004',
                'lat' => 40.4211,
                'lng' => -3.7043,
                'descripcion' => 'Estudio moderno en zona céntrica',
                'precio' => 800.00,
                'gastos' => json_encode(['agua' => 25, 'luz' => 40, 'comunidad' => 30]),
                'estado' => 'publicada',
            ],
            // Arrendador 2
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Av. Diagonal',
                'direccion' => 'Av. Diagonal 88',
                'ciudad' => 'Barcelona',
                'cp' => '08008',
                'lat' => 41.3947,
                'lng' => 2.1538,
                'descripcion' => 'Apartamento en la avenida Diagonal',
                'precio' => 1500.00,
                'gastos' => json_encode(['agua' => 30, 'luz' => 55, 'comunidad' => 45]),
                'estado' => 'alquilada',
            ],
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Calle Pelai',
                'direccion' => 'Calle Pelai 12',
                'ciudad' => 'Barcelona',
                'cp' => '08001',
                'lat' => 41.3979,
                'lng' => 2.1674,
                'descripcion' => 'Piso luminoso en el corazón de Barcelona',
                'precio' => 1100.00,
                'gastos' => json_encode(['agua' => 28, 'luz' => 48, 'comunidad' => 38]),
                'estado' => 'alquilada',
            ],
            // Arrendador 1
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Lujo Paseo de Gracia',
                'direccion' => 'Paseo de Gracia 5',
                'ciudad' => 'Barcelona',
                'cp' => '08007',
                'lat' => 41.3917,
                'lng' => 2.1649,
                'descripcion' => 'Piso de lujo en Paseo de Gracia',
                'precio' => 2200.00,
                'gastos' => json_encode(['agua' => 40, 'luz' => 70, 'comunidad' => 60]),
                'estado' => 'publicada',
            ],
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Centro Málaga',
                'direccion' => 'Calle Larios 7',
                'ciudad' => 'Málaga',
                'cp' => '29005',
                'lat' => 36.7202,
                'lng' => -4.4213,
                'descripcion' => 'Piso céntrico en Málaga',
                'precio' => 820.00,
                'gastos' => json_encode(['agua' => 25, 'luz' => 42, 'comunidad' => 35]),
                'estado' => 'publicada',
            ],
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Historic Sevilla',
                'direccion' => 'Alameda de Hércules 3',
                'ciudad' => 'Sevilla',
                'cp' => '41002',
                'lat' => 37.3831,
                'lng' => -5.9754,
                'descripcion' => 'Piso en zona histórica de Sevilla',
                'precio' => 650.00,
                'gastos' => json_encode(['agua' => 20, 'luz' => 38, 'comunidad' => 28]),
                'estado' => 'publicada',
            ],
            // Arrendador 2
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Valencia',
                'direccion' => 'Calle Colón 8',
                'ciudad' => 'Valencia',
                'cp' => '46004',
                'lat' => 39.4697,
                'lng' => -0.3763,
                'descripcion' => 'Piso en zona céntrica de Valencia',
                'precio' => 750.00,
                'gastos' => json_encode(['agua' => 25, 'luz' => 45, 'comunidad' => 35]),
                'estado' => 'borrador',
            ],
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Gran Vía',
                'direccion' => 'Gran Vía 45',
                'ciudad' => 'Bilbao',
                'cp' => '48001',
                'lat' => 43.2630,
                'lng' => -2.9350,
                'descripcion' => 'Piso en Gran Vía de Bilbao',
                'precio' => 900.00,
                'gastos' => json_encode(['agua' => 28, 'luz' => 50, 'comunidad' => 40]),
                'estado' => 'inactiva',
            ],
            // Propiedades adicionales (11-15)
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Zaragoza',
                'direccion' => 'Calle Coso 15',
                'ciudad' => 'Zaragoza',
                'cp' => '50001',
                'lat' => 41.6563,
                'lng' => -0.8773,
                'descripcion' => 'Piso en el centro de Zaragoza',
                'precio' => 650.00,
                'gastos' => json_encode(['agua' => 22, 'luz' => 40, 'comunidad' => 30]),
                'estado' => 'publicada',
            ],
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Alicante',
                'direccion' => 'Paseo de la Explanada 3',
                'ciudad' => 'Alicante',
                'cp' => '03002',
                'lat' => 38.3452,
                'lng' => -0.4810,
                'descripcion' => 'Piso con vistas a la bahía',
                'precio' => 1200.00,
                'gastos' => json_encode(['agua' => 30, 'luz' => 55, 'comunidad' => 45]),
                'estado' => 'publicada',
            ],
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Granada',
                'direccion' => 'Calle Reyes Católicos 12',
                'ciudad' => 'Granada',
                'cp' => '18009',
                'lat' => 37.1773,
                'lng' => -3.5986,
                'descripcion' => 'Piso histórico en Granada',
                'precio' => 700.00,
                'gastos' => json_encode(['agua' => 20, 'luz' => 38, 'comunidad' => 28]),
                'estado' => 'borrador',
            ],
            [
                'arrendador_email' => 'arrendador@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Murcia',
                'direccion' => 'Plaza de las Flores 7',
                'ciudad' => 'Murcia',
                'cp' => '30002',
                'lat' => 37.9922,
                'lng' => -1.1307,
                'descripcion' => 'Piso céntrico en Murcia',
                'precio' => 550.00,
                'gastos' => json_encode(['agua' => 18, 'luz' => 35, 'comunidad' => 25]),
                'estado' => 'inactiva',
            ],
            [
                'arrendador_email' => 'admin@spotstay.com',
                'gestor_email' => 'admin@spotstay.com',
                'titulo' => 'Piso Valladolid',
                'direccion' => 'Calle Miguel Íscar 15',
                'ciudad' => 'Valladolid',
                'cp' => '47001',
                'lat' => 41.6510,
                'lng' => -4.7245,
                'descripcion' => 'Piso en zona comercial de Valladolid',
                'precio' => 1400.00,
                'gastos' => json_encode(['agua' => 32, 'luz' => 58, 'comunidad' => 48]),
                'estado' => 'publicada',
            ],
        ];

        foreach ($propiedades as $prop) {
            $idArrendador = DB::table('tbl_usuario')
                ->where('email_usuario', $prop['arrendador_email'])
                ->value('id_usuario');

            $idGestor = DB::table('tbl_usuario')
                ->where('email_usuario', $prop['gestor_email'])
                ->value('id_usuario');

            unset($prop['arrendador_email'], $prop['gestor_email']);

            DB::table('tbl_propiedad')->insert([
                'titulo_propiedad' => $prop['titulo'],
                'direccion_propiedad' => $prop['direccion'],
                'ciudad_propiedad' => $prop['ciudad'],
                'codigo_postal_propiedad' => $prop['cp'],
                'latitud_propiedad' => $prop['lat'],
                'longitud_propiedad' => $prop['lng'],
                'descripcion_propiedad' => $prop['descripcion'],
                'precio_propiedad' => $prop['precio'],
                'gastos_propiedad' => $prop['gastos'],
                'estado_propiedad' => $prop['estado'],
                'id_arrendador_fk' => $idArrendador,
                'id_gestor_fk' => $idGestor,
                'creado_propiedad' => Carbon::now(),
            ]);
        }
    }
}
