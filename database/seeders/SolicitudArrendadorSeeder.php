<?php

namespace Database\Seeders;

use App\Models\SolicitudArrendador;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SolicitudArrendadorSeeder extends Seeder
{
    public function run(): void
    {
        $solicitudes = [
            ['usuario' => 'test1@example.com', 'nombre_empresa' => 'Empresa Inmobiliaria A', 'estado' => 'pendiente', 'ciudad' => 'Madrid', 'direccion' => 'Calle Gran Vía 123'],
            ['usuario' => 'test2@example.com', 'nombre_empresa' => 'Gestiones Llave', 'estado' => 'aprobada', 'ciudad' => 'Barcelona', 'direccion' => 'Paseo de Gracia 456'],
            ['usuario' => 'inquilino1@example.com', 'nombre_empresa' => 'Mi Negocio Inmobiliario', 'estado' => 'rechazada', 'ciudad' => 'Valencia', 'direccion' => 'Calle Colón 789'],
            ['usuario' => 'inquilino2@example.com', 'nombre_empresa' => 'Alquileres Rápidos', 'estado' => 'pendiente', 'ciudad' => 'Madrid', 'direccion' => 'Avenida Castellana 321'],
            ['usuario' => 'inquilino3@example.com', 'nombre_empresa' => 'PropiedadesTOP', 'estado' => 'aprobada', 'ciudad' => 'Sevilla', 'direccion' => 'Calle Betis 654'],
            ['usuario' => 'inquilino4@example.com', 'nombre_empresa' => 'Casa Perfecta', 'estado' => 'pendiente', 'ciudad' => 'Bilbao', 'direccion' => 'Gran Vía 987'],
            ['usuario' => 'inquilino5@example.com', 'nombre_empresa' => 'Viviendas Premium', 'estado' => 'aprobada', 'ciudad' => 'Madrid', 'direccion' => 'Calle Serrano 111'],
            ['usuario' => 'inquilino6@example.com', 'nombre_empresa' => 'Inmobiliaria Express', 'estado' => 'rechazada', 'ciudad' => 'Barcelona', 'direccion' => 'Avenida Diagonal 222'],
            ['usuario' => 'inquilino7@example.com', 'nombre_empresa' => 'Alquileres Seguros', 'estado' => 'pendiente', 'ciudad' => 'Valencia', 'direccion' => 'Calle Xátiva 333'],
            ['usuario' => 'inquilino8@example.com', 'nombre_empresa' => 'Gestión Total', 'estado' => 'aprobada', 'ciudad' => 'Madrid', 'direccion' => 'Plaza Mayor 444'],
            ['usuario' => 'test1@example.com', 'nombre_empresa' => 'Nueva Inversión', 'estado' => 'pendiente', 'ciudad' => 'Sevilla', 'direccion' => 'Calle Sierpes 555'],
            ['usuario' => 'test2@example.com', 'nombre_empresa' => 'Soluciones Vivienda', 'estado' => 'aprobada', 'ciudad' => 'Bilbao', 'direccion' => 'Calle Ibáñez de Bilbao 666'],
            ['usuario' => 'inquilino1@example.com', 'nombre_empresa' => 'Casas para Todos', 'estado' => 'pendiente', 'ciudad' => 'Barcelona', 'direccion' => 'Ramblas 777'],
            ['usuario' => 'inquilino2@example.com', 'nombre_empresa' => 'Propiedades Net', 'estado' => 'rechazada', 'ciudad' => 'Madrid', 'direccion' => 'Calle Sol 888'],
            ['usuario' => 'inquilino3@example.com', 'nombre_empresa' => 'Smart Alquileres', 'estado' => 'aprobada', 'ciudad' => 'Valencia', 'direccion' => 'Calle Paz 999'],
            ['usuario' => 'inquilino4@example.com', 'nombre_empresa' => 'Vivienda Digital', 'estado' => 'pendiente', 'ciudad' => 'Sevilla', 'direccion' => 'Avenida América 101'],
            ['usuario' => 'inquilino5@example.com', 'nombre_empresa' => 'Casa Segura', 'estado' => 'pendiente', 'ciudad' => 'Bilbao', 'direccion' => 'Calle Autonomía 102'],
            ['usuario' => 'inquilino6@example.com', 'nombre_empresa' => 'Inversión Inteligente', 'estado' => 'aprobada', 'ciudad' => 'Barcelona', 'direccion' => 'Calle Valencia 103'],
            ['usuario' => 'inquilino7@example.com', 'nombre_empresa' => 'Alquileres Premium', 'estado' => 'rechazada', 'ciudad' => 'Madrid', 'direccion' => 'Calle Atocha 104'],
            ['usuario' => 'inquilino8@example.com', 'nombre_empresa' => 'Gestión Profesional', 'estado' => 'aprobada', 'ciudad' => 'Valencia', 'direccion' => 'Calle San Vicente 105'],
        ];

        $admins = Usuario::whereHas('roles', function ($q) {
            $q->where('nombre_rol', 'admin');
        })->get();

        $adminIndex = 0;

        foreach ($solicitudes as $data) {
            $usuario = Usuario::where('email_usuario', $data['usuario'])->first();
            if ($usuario) {
                $admin = null;
                if ($adminIndex < count($admins)) {
                    $admin = $admins[$adminIndex];
                    $adminIndex++;
                }

                SolicitudArrendador::firstOrCreate(
                    ['id_usuario_fk' => $usuario->id_usuario, 'estado_solicitud_arrendador' => $data['estado']],
                    [
                        'datos_solicitud_arrendador' => json_encode([
                            'nombre_empresa' => $data['nombre_empresa'],
                            'ciudad' => $data['ciudad'],
                            'direccion' => $data['direccion'],
                            'fecha_solicitud' => now()->subDays(5)->toDateString(),
                        ]),
                        'id_admin_revisa_fk' => $admin?->id_usuario,
                        'notas_solicitud_arrendador' => $data['estado'] === 'rechazada' ? 'Documentación incompleta' : null,
                        'creado_solicitud_arrendador' => now()->subDays(5),
                        'actualizado_solicitud_arrendador' => now(),
                    ]
                );
            }
        }
    }
}