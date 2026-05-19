<?php

namespace Database\Seeders;

use App\Models\SolicitudArrendador;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SolicitudArrendadorSeeder extends Seeder
{
    public function run(): void
    {
        // Usar emails reales de inquilinos del UsuarioSeeder
        $solicitudes = [
            ['email' => 'dsuarez@spotstay.com', 'nombre_empresa' => 'Empresa Inmobiliaria A', 'estado' => 'pendiente', 'ciudad' => 'Madrid', 'direccion' => 'Calle Gran Vía 123'],
            ['email' => 'lmartinez@spotstay.com', 'nombre_empresa' => 'Gestiones Llave', 'estado' => 'aprobada', 'ciudad' => 'Barcelona', 'direccion' => 'Paseo de Gracia 456'],
            ['email' => 'plopez@spotstay.com', 'nombre_empresa' => 'Mi Negocio Inmobiliario', 'estado' => 'rechazada', 'ciudad' => 'Valencia', 'direccion' => 'Calle Colón 789'],
            ['email' => 'msanchez@spotstay.com', 'nombre_empresa' => 'Alquileres Rápidos', 'estado' => 'pendiente', 'ciudad' => 'Madrid', 'direccion' => 'Avenida Castellana 321'],
            ['email' => 'fperez@spotstay.com', 'nombre_empresa' => 'PropiedadesTOP', 'estado' => 'aprobada', 'ciudad' => 'Sevilla', 'direccion' => 'Calle Betis 654'],
            ['email' => 'agarcia@spotstay.com', 'nombre_empresa' => 'Casa Perfecta', 'estado' => 'pendiente', 'ciudad' => 'Bilbao', 'direccion' => 'Gran Vía 987'],
            ['email' => 'jgonzalez@spotstay.com', 'nombre_empresa' => 'Viviendas Premium', 'estado' => 'aprobada', 'ciudad' => 'Madrid', 'direccion' => 'Calle Serrano 111'],
            ['email' => 'vrodriguez@spotstay.com', 'nombre_empresa' => 'Inmobiliaria Express', 'estado' => 'rechazada', 'ciudad' => 'Barcelona', 'direccion' => 'Avenida Diagonal 222'],
            ['email' => 'pfernandez@spotstay.com', 'nombre_empresa' => 'Alquileres Seguros', 'estado' => 'pendiente', 'ciudad' => 'Valencia', 'direccion' => 'Calle Xátiva 333'],
            ['email' => 'rdiez@spotstay.com', 'nombre_empresa' => 'Gestión Total', 'estado' => 'aprobada', 'ciudad' => 'Madrid', 'direccion' => 'Plaza Mayor 444'],
            ['email' => 'therrera@spotstay.com', 'nombre_empresa' => 'Nueva Inversión', 'estado' => 'pendiente', 'ciudad' => 'Sevilla', 'direccion' => 'Calle Sierpes 555'],
            ['email' => 'ijimenez@spotstay.com', 'nombre_empresa' => 'Soluciones Vivienda', 'estado' => 'aprobada', 'ciudad' => 'Bilbao', 'direccion' => 'Calle Ibáñez de Bilbao 666'],
            ['email' => 'amolina@spotstay.com', 'nombre_empresa' => 'Casas para Todos', 'estado' => 'pendiente', 'ciudad' => 'Barcelona', 'direccion' => 'Ramblas 777'],
            ['email' => 'rvega@spotstay.com', 'nombre_empresa' => 'Propiedades Net', 'estado' => 'rechazada', 'ciudad' => 'Madrid', 'direccion' => 'Calle Sol 888'],
            ['email' => 'rmora@spotstay.com', 'nombre_empresa' => 'Smart Alquileres', 'estado' => 'aprobada', 'ciudad' => 'Valencia', 'direccion' => 'Calle Paz 999'],
        ];

        $admins = Usuario::whereHas('roles', function ($q) {
            $q->where('slug_rol', 'admin');
        })->get();

        $adminIndex = 0;

        foreach ($solicitudes as $data) {
            // Ajuste para el nuevo correo de Amanda García si es necesario
            $email = ($data['email'] === 'agarcia@spotstay.com') ? 'amagarcia@spotstay.com' : $data['email'];
            
            $usuario = Usuario::where('email_usuario', $email)->first();
            
            // SOLO creamos solicitud si el usuario NO es admin
            if ($usuario && !$usuario->roles()->where('slug_rol', 'admin')->exists()) {
                $admin = null;
                if (!$admins->isEmpty()) {
                    $admin = $admins->get($adminIndex % $admins->count());
                    $adminIndex++;
                }

                // Generar datos ficticios normalizados
                $dni = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . strtoupper(chr(rand(65, 90)));
                $nif = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . strtoupper(chr(rand(65, 90)));
                $iban = 'ES' . str_pad(rand(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
                $fechaNacimiento = \Carbon\Carbon::now()->subYears(rand(30, 70))->format('Y-m-d');
                $fechaSolicitud = now()->subDays(5)->toDateString();

                SolicitudArrendador::firstOrCreate(
                    ['id_usuario_fk' => $usuario->id_usuario, 'estado_solicitud_arrendador' => $data['estado']],
                    [
                        'telefono_solicitud' => '+34 ' . rand(600, 699) . ' ' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                        'fecha_nacimiento_solicitud' => $fechaNacimiento,
                        'tipo_documento_solicitud' => 'DNI',
                        'numero_documento_solicitud' => $dni,
                        'iban_solicitud' => $iban,
                        'titular_cuenta_solicitud' => $usuario->nombre_usuario,
                        'nif_solicitud' => $nif,
                        'direccion_fiscal_solicitud' => $data['direccion'] . ', ' . $data['ciudad'],
                        'tipo_arrendador_solicitud' => 'empresa',
                        'descripcion_solicitud' => 'Empresa dedicada a la gestión inmobiliaria: ' . $data['nombre_empresa'],
                        'num_propiedades_previstas_solicitud' => rand(1, 15),
                        'es_propietario_solicitud' => (bool) rand(0, 1),
                        'acepta_terminos_solicitud' => true,
                        'acepta_veracidad_solicitud' => true,
                        'fecha_aceptacion_solicitud' => $data['estado'] !== 'pendiente' ? $fechaSolicitud : null,
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