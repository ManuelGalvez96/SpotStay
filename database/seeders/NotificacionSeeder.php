<?php

namespace Database\Seeders;

use App\Models\Notificacion;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $notificaciones = [
            ['tipo' => 'pago_vencido', 'mensaje' => 'Tu pago de alquiler está vencido'],
            ['tipo' => 'nuevo_mensaje', 'mensaje' => 'Tienes un nuevo mensaje'],
            ['tipo' => 'reparacion_urgente', 'mensaje' => 'Reparación urgente solicitada'],
            ['tipo' => 'propiedad_publicada', 'mensaje' => 'Tu propiedad ha sido publicada'],
            ['tipo' => 'incidencia_resuelta', 'mensaje' => 'Tu incidencia ha sido resuelta'],
            ['tipo' => 'contrato_vencimiento', 'mensaje' => 'Tu contrato vence en 30 días'],
            ['tipo' => 'nuevo_inquilino', 'mensaje' => 'Se aprobó tu solicitud de alquiler'],
            ['tipo' => 'solicitud_aprobada', 'mensaje' => 'Tu solicitud como arrendador fue aprobada'],
            ['tipo' => 'cambio_propiedad', 'mensaje' => 'Se realizó cambio en tu propiedad'],
            ['tipo' => 'inspeccion_programada', 'mensaje' => 'Inspección programada para mañana'],
            ['tipo' => 'pago_recibido', 'mensaje' => 'Se recibió tu pago correctamente'],
            ['tipo' => 'documento_requerido', 'mensaje' => 'Se requieren documentos adicionales'],
            ['tipo' => 'propiedad_alquilada', 'mensaje' => 'Tu propiedad fue alquilada'],
            ['tipo' => 'actualizacion_propiedad', 'mensaje' => 'Se actualizó la información de tu propiedad'],
            ['tipo' => 'nuevo_residente', 'mensaje' => 'Bienvenido nuevo residente'],
            ['tipo' => 'recordatorio_pago', 'mensaje' => 'Recordatorio: tu pago vence en 3 días'],
            ['tipo' => 'incidencia_reportada', 'mensaje' => 'Nueva incidencia reportada'],
            ['tipo' => 'contrato_firmado', 'mensaje' => 'Contrato firmado exitosamente'],
            ['tipo' => 'cancelacion_solicitada', 'mensaje' => 'Cancelación de alquiler solicitada'],
            ['tipo' => 'renovacion_exitosa', 'mensaje' => 'Contrato renovado exitosamente'],
        ];

        $usuarios = Usuario::all();
        $usuarioIndex = 0;

        foreach ($notificaciones as $data) {
            if ($usuarioIndex < count($usuarios)) {
                $usuario = $usuarios[$usuarioIndex];
                $esLeida = (bool) rand(0, 1);
                
                Notificacion::firstOrCreate(
                    ['id_usuario_fk' => $usuario->id_usuario, 'tipo_notificacion' => $data['tipo']],
                    [
                        'datos_notificacion' => json_encode(['mensaje' => $data['mensaje']]),
                        'leida_notificacion' => $esLeida,
                        'leida_en_notificacion' => $esLeida ? now()->subDays(rand(1, 7)) : null,
                        'creado_notificacion' => now()->subDays(rand(1, 7)),
                        'actualizado_notificacion' => now(),
                    ]
                );
                $usuarioIndex++;
            }
        }
    }
}