<?php

namespace Database\Seeders;

use App\Models\SolicitudGestor;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class SolicitudGestorSeeder extends Seeder
{
    public function run(): void
    {
        $solicitudes = [
            ['email' => 'rdiaz@spotstay.com', 'estado' => 'pendiente', 'descripcion' => 'Quiero gestionar incidencias y dar soporte a propietarios con seguimiento diario.', 'experiencia' => 'He coordinado pequeños equipos de mantenimiento durante 2 años.'],
            ['email' => 'csanchez@spotstay.com', 'estado' => 'aprobada', 'descripcion' => 'Tengo experiencia en atención al cliente y coordinación de servicios técnicos.', 'experiencia' => '3 años gestionando proveedores y incidencias en una empresa de servicios.'],
            ['email' => 'fperez@spotstay.com', 'estado' => 'rechazada', 'descripcion' => 'Busco incorporarme al equipo para aprender y gestionar propiedades.', 'experiencia' => 'Sin experiencia previa directa, pero con formación en administración.'],
            ['email' => 'bgonzalez@spotstay.com', 'estado' => 'pendiente', 'descripcion' => 'Puedo encargarme de la comunicación con inquilinos y seguimiento de incidencias.', 'experiencia' => 'Atención al público y coordinación básica de reparaciones.'],
            ['email' => 'drodriguez@spotstay.com', 'estado' => 'aprobada', 'descripcion' => 'Me interesa administrar tareas de soporte, informes y control de incidencias.', 'experiencia' => 'He trabajado 4 años en gestión administrativa y soporte operativo.'],
            ['email' => 'jlavignole@spotstay.com', 'estado' => 'pendiente', 'descripcion' => 'Quiero apoyar a la empresa en la gestión de incidencias y seguimiento de propiedades.', 'experiencia' => 'Experiencia previa en coordinación de alquileres y trato con clientes.'],
            ['email' => 'ivazquez@spotstay.com', 'estado' => 'rechazada', 'descripcion' => 'Tengo interés en formar parte del equipo de gestión interna.', 'experiencia' => 'He realizado tareas de apoyo administrativo durante 1 año.'],
            ['email' => 'mgarcia@spotstay.com', 'estado' => 'aprobada', 'descripcion' => 'Puedo supervisar incidencias, dar soporte y controlar tiempos de resolución.', 'experiencia' => 'Trabajo en administración de fincas desde hace 5 años.'],
            ['email' => 'snebot@spotstay.com', 'estado' => 'pendiente', 'descripcion' => 'Me gustaría colaborar con tareas de seguimiento y soporte de usuarios.', 'experiencia' => 'He coordinado pequeños proyectos y atención telefónica.'],
            ['email' => 'amolina@spotstay.com', 'estado' => 'aprobada', 'descripcion' => 'Tengo perfil organizado para gestionar incidencias, documentación y avisos.', 'experiencia' => '2 años de experiencia en logística y gestión de tareas.'],
        ];

        $admins = Usuario::whereHas('roles', function ($consulta) {
            $consulta->where('slug_rol', 'admin');
        })->get();

        if ($admins->isEmpty()) {
            return;
        }

        foreach ($solicitudes as $indice => $data) {
            $usuario = Usuario::where('email_usuario', $data['email'])->first();

            if (!$usuario) {
                continue;
            }

            $admin = $admins->get($indice % $admins->count());
            $ahora = now()->subDays(10 - $indice);
            $fechaAceptacion = $data['estado'] === 'pendiente' ? null : $ahora->copy()->subDay();

            SolicitudGestor::firstOrCreate(
                ['id_usuario_fk' => $usuario->id_usuario],
                [
                    'id_admin_revisa_fk' => $data['estado'] === 'pendiente' ? null : $admin->id_usuario,
                    'descripcion_solicitud' => $data['descripcion'],
                    'experiencia_solicitud' => $data['experiencia'],
                    'acepta_terminos_solicitud' => true,
                    'acepta_veracidad_solicitud' => true,
                    'fecha_aceptacion_solicitud' => $fechaAceptacion,
                    'estado_solicitud_gestor' => $data['estado'],
                    'notas_solicitud_gestor' => $data['estado'] === 'rechazada' ? 'Perfil no alineado con la experiencia solicitada.' : null,
                    'creado_solicitud_gestor' => $ahora,
                    'actualizado_solicitud_gestor' => $ahora,
                ]
            );
        }
    }
}