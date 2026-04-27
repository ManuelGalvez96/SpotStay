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
            // Para Inquilinos (Laura, Pedro, Sofía)
            [
                'usuario_email' => 'lmartinez@spotstay.com',
                'tipo_notificacion' => 'pago_vencido',
                'titulo' => 'Pago Vencido',
                'mensaje' => 'Tu pago de alquiler está vencido. Por favor, realiza el pago lo antes posible.',
                'url' => '/mis-pagos',
                'icono' => 'fas-exclamation-triangle',
                'color' => '#ff6b6b',
                'tipo_entidad' => 'alquiler',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'plopez@spotstay.com',
                'tipo_notificacion' => 'nuevo_mensaje',
                'titulo' => 'Nuevo Mensaje',
                'mensaje' => 'Has recibido un nuevo mensaje del arrendador sobre tu alquiler.',
                'url' => '/mensajes',
                'icono' => 'fas-envelope',
                'color' => '#4ecdc4',
                'tipo_entidad' => 'conversacion',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'msanchez@spotstay.com',
                'tipo_notificacion' => 'incidencia_resuelta',
                'titulo' => 'Incidencia Resuelta',
                'mensaje' => 'Tu incidencia reportada ha sido resuelta. Verificá los detalles.',
                'url' => '/mis-incidencias',
                'icono' => 'fas-check-circle',
                'color' => '#51cf66',
                'tipo_entidad' => 'incidencia',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'dsuarez@spotstay.com',
                'tipo_notificacion' => 'contrato_vencimiento',
                'titulo' => 'Contrato Próximo a Vencer',
                'mensaje' => 'Tu contrato vence en 30 días. Considera renovar si deseas continuar.',
                'url' => '/mis-contratos',
                'icono' => 'fas-calendar-times',
                'color' => '#ff922b',
                'tipo_entidad' => 'contrato',
                'id_entidad' => null,
            ],

            // Para Arrendadores (Carlos, Elena, Roberto, Jaume)
            [
                'usuario_email' => 'jlavignole@spotstay.com',
                'tipo_notificacion' => 'nuevo_inquilino',
                'titulo' => 'Nueva Solicitud de Alquiler',
                'mensaje' => 'Se aprobó la solicitud de alquiler para una de tus propiedades.',
                'url' => '/mis-alquileres',
                'icono' => 'fas-home',
                'color' => '#748ffc',
                'tipo_entidad' => 'alquiler',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'ivazquez@spotstay.com',
                'tipo_notificacion' => 'propiedad_publicada',
                'titulo' => 'Propiedad Publicada',
                'mensaje' => 'Tu propiedad ha sido aprobada y publicada en el sistema.',
                'url' => '/mis-propiedades',
                'icono' => 'fas-building',
                'color' => '#a78bfa',
                'tipo_entidad' => 'propiedad',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'eruiz@spotstay.com',
                'tipo_notificacion' => 'pago_recibido',
                'titulo' => 'Pago Recibido',
                'mensaje' => 'Has recibido un pago de alquiler de uno de tus inquilinos.',
                'url' => '/mis-pagos',
                'icono' => 'fas-check-double',
                'color' => '#51cf66',
                'tipo_entidad' => 'pago',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'mgarcia@spotstay.com',
                'tipo_notificacion' => 'documento_requerido',
                'titulo' => 'Documentos Requeridos',
                'mensaje' => 'Se requieren documentos adicionales para tu solicitud como arrendador.',
                'url' => '/solicitud-arrendador',
                'icono' => 'fas-file-upload',
                'color' => '#fcc419',
                'tipo_entidad' => 'solicitud',
                'id_entidad' => null,
            ],

            // Para Gestores (Miguel, Ana, Carlos)
            [
                'usuario_email' => 'mgestor@spotstay.com',
                'tipo_notificacion' => 'incidencia_reportada',
                'titulo' => 'Nueva Incidencia Reportada',
                'mensaje' => 'Se ha reportado una nueva incidencia que requiere atención.',
                'url' => '/gestionar/incidencias',
                'icono' => 'fas-exclamation-circle',
                'color' => '#ff6b6b',
                'tipo_entidad' => 'incidencia',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'afernandez@spotstay.com',
                'tipo_notificacion' => 'propiedad_alquilada',
                'titulo' => 'Propiedad Alquilada',
                'mensaje' => 'Una propiedad bajo tu gestión ha sido alquilada exitosamente.',
                'url' => '/gestionar/propiedades',
                'icono' => 'fas-handshake',
                'color' => '#51cf66',
                'tipo_entidad' => 'alquiler',
                'id_entidad' => null,
            ],

            // Para Admin (6 notificaciones)
            [
                'usuario_email' => 'agarcia@spotstay.com',
                'tipo_notificacion' => 'solicitud_arrendador_pendiente',
                'titulo' => 'Solicitud de Arrendador Pendiente',
                'mensaje' => 'Existe una nueva solicitud de arrendador para revisar.',
                'url' => '/admin/solicitudes',
                'icono' => 'fas-clipboard-list',
                'color' => '#fcc419',
                'tipo_entidad' => 'solicitud',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'alopez@spotstay.com',
                'tipo_notificacion' => 'propiedad_pendiente_aprobacion',
                'titulo' => 'Propiedad Pendiente de Aprobación',
                'mensaje' => 'Una propiedad está pendiente de aprobación administrativa.',
                'url' => '/admin/propiedades',
                'icono' => 'fas-building',
                'color' => '#748ffc',
                'tipo_entidad' => 'propiedad',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'amartinez@spotstay.com',
                'tipo_notificacion' => 'alerta_sistema',
                'titulo' => 'Alerta de Sistema',
                'mensaje' => 'Se ha detectado actividad inusual en el sistema que requiere revisión.',
                'url' => '/admin/dashboard',
                'icono' => 'fas-bell',
                'color' => '#ff6b6b',
                'tipo_entidad' => 'sistema',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'agarcia@spotstay.com',
                'tipo_notificacion' => 'estadística_semanal',
                'titulo' => 'Reporte Semanal',
                'mensaje' => 'Tu reporte estadístico semanal está listo para consulta.',
                'url' => '/admin/reportes',
                'icono' => 'fas-chart-bar',
                'color' => '#4ecdc4',
                'tipo_entidad' => 'sistema',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'alopez@spotstay.com',
                'tipo_notificacion' => 'incidencia_critica',
                'titulo' => 'Incidencia Crítica Reportada',
                'mensaje' => 'Una incidencia crítica ha sido reportada en el sistema.',
                'url' => '/admin/incidencias',
                'icono' => 'fas-exclamation',
                'color' => '#ff6b6b',
                'tipo_entidad' => 'incidencia',
                'id_entidad' => null,
            ],
            [
                'usuario_email' => 'amartinez@spotstay.com',
                'tipo_notificacion' => 'mantenimiento_programado',
                'titulo' => 'Mantenimiento Programado',
                'mensaje' => 'Se ha completado el mantenimiento programado del sistema.',
                'url' => '/admin/dashboard',
                'icono' => 'fas-tools',
                'color' => '#51cf66',
                'tipo_entidad' => 'sistema',
                'id_entidad' => null,
            ],
        ];

        foreach ($notificaciones as $data) {
            $usuario = Usuario::where('email_usuario', $data['usuario_email'])->first();
            if ($usuario) {
                $esLeida = (bool) rand(0, 1);
                
                Notificacion::firstOrCreate(
                    [
                        'id_usuario_fk' => $usuario->id_usuario,
                        'tipo_notificacion' => $data['tipo_notificacion'],
                        'titulo_notificacion' => $data['titulo'],
                    ],
                    [
                        'mensaje_notificacion' => $data['mensaje'],
                        'url_notificacion' => $data['url'],
                        'icono_notificacion' => $data['icono'],
                        'color_notificacion' => $data['color'],
                        'tipo_entidad_notificacion' => $data['tipo_entidad'],
                        'id_entidad_notificacion' => $data['id_entidad'],
                        'leida_notificacion' => $esLeida,
                        'leida_en_notificacion' => $esLeida ? now()->subDays(rand(1, 7)) : null,
                        'creado_notificacion' => now()->subDays(rand(1, 7)),
                        'actualizado_notificacion' => now(),
                    ]
                );
            }
        }
    }
}