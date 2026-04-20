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
            ['usuario' => 'test1@example.com', 'nombre_empresa' => 'Empresa Inmobiliaria A', 'estado' => 'pendiente'],
            ['usuario' => 'test2@example.com', 'nombre_empresa' => 'Gestiones Llave', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino1@example.com', 'nombre_empresa' => 'Mi Negocio Inmobiliario', 'estado' => 'rechazado'],
            ['usuario' => 'inquilino2@example.com', 'nombre_empresa' => 'Alquileres Rápidos', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino3@example.com', 'nombre_empresa' => 'PropiedadesTOP', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino4@example.com', 'nombre_empresa' => 'Casa Perfecta', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino5@example.com', 'nombre_empresa' => 'Viviendas Premium', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino6@example.com', 'nombre_empresa' => 'Inmobiliaria Express', 'estado' => 'rechazado'],
            ['usuario' => 'inquilino7@example.com', 'nombre_empresa' => 'Alquileres Seguros', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino8@example.com', 'nombre_empresa' => 'Gestión Total', 'estado' => 'aprobado'],
            ['usuario' => 'test1@example.com', 'nombre_empresa' => 'Nueva Inversión', 'estado' => 'pendiente'],
            ['usuario' => 'test2@example.com', 'nombre_empresa' => 'Soluciones Vivienda', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino1@example.com', 'nombre_empresa' => 'Casas para Todos', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino2@example.com', 'nombre_empresa' => 'Propiedades Net', 'estado' => 'rechazado'],
            ['usuario' => 'inquilino3@example.com', 'nombre_empresa' => 'Smart Alquileres', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino4@example.com', 'nombre_empresa' => 'Vivienda Digital', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino5@example.com', 'nombre_empresa' => 'Casa Segura', 'estado' => 'pendiente'],
            ['usuario' => 'inquilino6@example.com', 'nombre_empresa' => 'Inversión Inteligente', 'estado' => 'aprobado'],
            ['usuario' => 'inquilino7@example.com', 'nombre_empresa' => 'Alquileres Premium', 'estado' => 'rechazado'],
            ['usuario' => 'inquilino8@example.com', 'nombre_empresa' => 'Gestión Profesional', 'estado' => 'aprobado'],
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
                            'fecha_solicitud' => now()->subDays(5)->toDateString(),
                        ]),
                        'id_admin_revisa_fk' => $admin?->id_usuario,
                        'notas_solicitud_arrendador' => $data['estado'] === 'rechazado' ? 'Documentación incompleta' : null,
                        'creado_solicitud_arrendador' => now()->subDays(5),
                        'actualizado_solicitud_arrendador' => now(),
                    ]
                );
            }
        }
    }
}