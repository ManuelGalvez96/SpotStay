<?php

namespace Database\Seeders;

use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class PropiedadSeeder extends Seeder
{
    public function run(): void
    {
        $propiedades = [
            ['titulo' => 'Precioso Piso en Gran Vía', 'ciudad' => 'Madrid', 'cp' => '28013', 'direccion' => 'Calle Gran Vía 1', 'desc' => 'Moderno piso de 2 habitaciones con buena iluminación natural', 'precio' => 1500, 'estado' => 'activo', 'arrendador' => 'arrendador1@example.com', 'gestor' => 'gestor1@spotstay.com'],
            ['titulo' => 'Espacioso Piso 3 Habitaciones', 'ciudad' => 'Madrid', 'cp' => '28001', 'direccion' => 'Calle Alcalá 45', 'desc' => 'Apartamento amplio con terraza y vistas al centro', 'precio' => 2000, 'estado' => 'activo', 'arrendador' => 'arrendador1@example.com', 'gestor' => 'gestor1@spotstay.com'],
            ['titulo' => 'Apartamento en Paseo de Gracia', 'ciudad' => 'Barcelona', 'cp' => '08008', 'direccion' => 'Paseo de Gracia 100', 'desc' => 'Céntrico y bien ubicado, totalmente reformado', 'precio' => 1800, 'estado' => 'activo', 'arrendador' => 'arrendador2@example.com', 'gestor' => 'gestor2@spotstay.com'],
            ['titulo' => 'Pequeño Apartamento Còrsega', 'ciudad' => 'Barcelona', 'cp' => '08029', 'direccion' => 'Calle Còrsega 50', 'desc' => 'Estudio compacto ideal para una persona', 'precio' => 1200, 'estado' => 'activo', 'arrendador' => 'arrendador2@example.com', 'gestor' => 'gestor2@spotstay.com'],
            ['titulo' => 'Piso Malvarrosa Frente Playa', 'ciudad' => 'Valencia', 'cp' => '46011', 'direccion' => 'Avenida Malvarrosa 30', 'desc' => 'Con vistas al mar y acceso directo a la playa', 'precio' => 1400, 'estado' => 'activo', 'arrendador' => 'arrendador3@example.com', 'gestor' => 'gestor3@spotstay.com'],
            ['titulo' => 'Acogedor Piso Centro Valencia', 'ciudad' => 'Valencia', 'cp' => '46001', 'direccion' => 'Calle Serrería 20', 'desc' => 'Cerca de todos los servicios y transportes', 'precio' => 1000, 'estado' => 'activo', 'arrendador' => 'arrendador3@example.com', 'gestor' => 'gestor3@spotstay.com'],
            ['titulo' => 'Casa Señorial en Betis', 'ciudad' => 'Sevilla', 'cp' => '41010', 'direccion' => 'Calle Betis 60', 'desc' => 'Vivienda grande con patio interior y jardín', 'precio' => 1600, 'estado' => 'activo', 'arrendador' => 'arrendador4@example.com', 'gestor' => 'gestor1@spotstay.com'],
            ['titulo' => 'Piso Centro Histórico Sevilla', 'ciudad' => 'Sevilla', 'cp' => '41001', 'direccion' => 'Avenida Constitución 15', 'desc' => 'Ubicado en zona monumental y turística', 'precio' => 950, 'estado' => 'activo', 'arrendador' => 'arrendador4@example.com', 'gestor' => 'gestor2@spotstay.com'],
            ['titulo' => 'Moderno Piso Ercilla', 'ciudad' => 'Bilbao', 'cp' => '48009', 'direccion' => 'Calle Ercilla 5', 'desc' => 'Recién renovado con acabados de lujo', 'precio' => 1700, 'estado' => 'activo', 'arrendador' => 'arrendador5@example.com', 'gestor' => 'gestor3@spotstay.com'],
            ['titulo' => 'Apartamento Compacto Zabalburu', 'ciudad' => 'Bilbao', 'cp' => '48005', 'direccion' => 'Plaza Zabalburu 8', 'desc' => 'Perfecto para estudiantes o profesionales jóvenes', 'precio' => 1100, 'estado' => 'activo', 'arrendador' => 'arrendador5@example.com', 'gestor' => 'gestor1@spotstay.com'],
            ['titulo' => 'Lujoso Piso Serrano', 'ciudad' => 'Madrid', 'cp' => '28006', 'direccion' => 'Calle Serrano 80', 'desc' => 'Residencial exclusivo con excelentes servicios', 'precio' => 2200, 'estado' => 'activo', 'arrendador' => 'arrendador1@example.com', 'gestor' => null],
            ['titulo' => 'Piso Castellana Premium', 'ciudad' => 'Madrid', 'cp' => '28046', 'direccion' => 'Paseo de la Castellana 200', 'desc' => 'Zona premium con acceso a polígono de negocios', 'precio' => 2400, 'estado' => 'activo', 'arrendador' => 'arrendador2@example.com', 'gestor' => null],
            ['titulo' => 'Casa con jardín Diagònal', 'ciudad' => 'Barcelona', 'cp' => '08036', 'direccion' => 'Avenida Diagònal 400', 'desc' => 'Casa grande con jardín trasero y garaje', 'precio' => 2800, 'estado' => 'activo', 'arrendador' => 'arrendador3@example.com', 'gestor' => null],
            ['titulo' => 'Piso Puerto Valencia', 'ciudad' => 'Valencia', 'cp' => '46001', 'direccion' => 'Avenida del Puerto 50', 'desc' => 'Junto a las infraestructuras portuarias', 'precio' => 1300, 'estado' => 'activo', 'arrendador' => 'arrendador4@example.com', 'gestor' => null],
            ['titulo' => 'Piso Tetuán Sevilla', 'ciudad' => 'Sevilla', 'cp' => '41002', 'direccion' => 'Calle Tetuán 25', 'desc' => 'Barrio tradicional con aire auténtico', 'precio' => 1450, 'estado' => 'activo', 'arrendador' => 'arrendador5@example.com', 'gestor' => null],
            ['titulo' => 'Pequeño Apartamento América', 'ciudad' => 'Madrid', 'cp' => '28002', 'direccion' => 'Avenida de América 10', 'desc' => 'Abierto y luminoso en zona de negocios', 'precio' => 900, 'estado' => 'activo', 'arrendador' => 'arrendador1@example.com', 'gestor' => 'gestor2@spotstay.com'],
            ['titulo' => 'Piso Muntaner Barcelona', 'ciudad' => 'Barcelona', 'cp' => '08011', 'direccion' => 'Calle Muntaner 35', 'desc' => 'Con equipamientos modernos y climatización', 'precio' => 1650, 'estado' => 'activo', 'arrendador' => 'arrendador2@example.com', 'gestor' => 'gestor3@spotstay.com'],
            ['titulo' => 'Apartamento Colón Valencia', 'ciudad' => 'Valencia', 'cp' => '46003', 'direccion' => 'Calle Colón 70', 'desc' => 'Junto al Museo de Bellas Artes', 'precio' => 1550, 'estado' => 'activo', 'arrendador' => 'arrendador3@example.com', 'gestor' => 'gestor1@spotstay.com'],
            ['titulo' => 'Piso Lehendakari Bilbao', 'ciudad' => 'Bilbao', 'cp' => '48011', 'direccion' => 'Avenida Lehendakari Aguirre 60', 'desc' => 'En la avenida más importante de la ciudad', 'precio' => 1350, 'estado' => 'activo', 'arrendador' => 'arrendador4@example.com', 'gestor' => 'gestor2@spotstay.com'],
            ['titulo' => 'Casa Buhaira Sevilla', 'ciudad' => 'Sevilla', 'cp' => '41003', 'direccion' => 'Avenida de la Buhaira 12', 'desc' => 'Casa amplia con espacio exterior', 'precio' => 1500, 'estado' => 'activo', 'arrendador' => 'arrendador5@example.com', 'gestor' => 'gestor3@spotstay.com'],
        ];

        foreach ($propiedades as $data) {
            $arrendador = Usuario::where('email_usuario', $data['arrendador'])->first();
            $gestor = $data['gestor'] ? Usuario::where('email_usuario', $data['gestor'])->first() : null;

            if ($arrendador) {
                Propiedad::firstOrCreate(
                    ['direccion_propiedad' => $data['direccion'], 'id_arrendador_fk' => $arrendador->id_usuario],
                    [
                        'titulo_propiedad' => $data['titulo'],
                        'ciudad_propiedad' => $data['ciudad'],
                        'codigo_postal_propiedad' => $data['cp'],
                        'descripcion_propiedad' => $data['desc'] ?? $data['descripcion'] ?? 'Propiedad disponible para alquiler',
                        'precio_propiedad' => $data['precio'],
                        'estado_propiedad' => $data['estado'],
                        'id_arrendador_fk' => $arrendador->id_usuario,
                        'id_gestor_fk' => $gestor?->id_usuario,
                        'creado_propiedad' => now(),
                        'actualizado_propiedad' => now(),
                    ]
                );
            }
        }
    }
}