<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotStay - Incidencia</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f9fafb;">

    <!-- Contenedor principal -->
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <!-- ENCABEZADO CON COLOR -->
        <div style="background: linear-gradient(135deg, #1AA068 0%, #15824a 100%); padding: 40px 30px; text-align: center; color: white;">
            <div style="font-size: 28px; font-weight: bold; margin-bottom: 8px;">SpotStay</div>
            <div style="font-size: 14px; opacity: 0.95;">Gestión de incidencias inmobiliarias</div>
        </div>

        <!-- CUERPO DEL EMAIL -->
        <div style="padding: 40px 30px;">

            <!-- SALUDO -->
            <p style="font-size: 16px; margin-bottom: 10px;">Hola <strong>{{ $destinatarioNombre ?? 'contacto' }}</strong>,</p>

            <!-- MENSAJE PERSONALIZADO -->
            <div style="background-color: #f3f4f6; border-left: 4px solid #1AA068; padding: 15px; margin-bottom: 30px; border-radius: 4px; font-size: 15px; line-height: 1.6;">
                {{ $mensaje }}
            </div>

            <!-- TARJETA DE INCIDENCIA CON COLOR POR PRIORIDAD -->
            @php
                $colorPrioridad = match($incidencia->prioridad_incidencia ?? 'media') {
                    'urgente' => '#EF4444',
                    'alta' => '#D97706',
                    'media' => '#6B7280',
                    'baja' => '#1AA068',
                    default => '#6B7280'
                };
                $labelPrioridad = ucfirst($incidencia->prioridad_incidencia ?? 'media');
            @endphp

            <div style="border-left: 5px solid {{ $colorPrioridad }}; background-color: #fafafa; padding: 20px; margin-bottom: 30px; border-radius: 4px;">

                <!-- Prioridad Badge -->
                <div style="margin-bottom: 15px;">
                    <span style="display: inline-block; background-color: {{ $colorPrioridad }}; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">
                        {{ $labelPrioridad }}
                    </span>
                </div>

                <!-- Título -->
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0; font-size: 18px; font-weight: bold; color: #1f2937;">
                        {{ $incidencia->titulo_incidencia ?? 'Sin título' }}
                    </p>
                </div>

                <!-- Detalles -->
                <div style="font-size: 14px; line-height: 1.8; color: #555;">
                    <div style="margin-bottom: 10px;">
                        <strong>Descripción:</strong><br>
                        {{ $incidencia->descripcion_incidencia ?? 'Sin descripción' }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Categoría:</strong><br>
                        {{ $incidencia->nombre_categoria ?? 'Sin categoría' }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Propiedad:</strong><br>
                        {{ $incidencia->direccion_propiedad ?? 'N/A' }} — {{ $incidencia->ciudad_propiedad ?? 'N/A' }}
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>Fecha de reporte:</strong><br>
                        {{ $incidencia->creado_incidencia ?? 'N/A' }}
                    </div>
                </div>

            </div>

            <!-- BOTÓN CTA -->
            <div style="text-align: center; margin-bottom: 30px;">
                <a href="{{ env('APP_URL') }}/admin/incidencias" style="display: inline-block; background-color: #1AA068; color: white; padding: 14px 32px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; transition: background-color 0.3s ease;">
                    Ver incidencia en SpotStay
                </a>
            </div>

            <!-- SEPARADOR -->
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

            <!-- PIE DE PÁGINA -->
            <div style="font-size: 12px; color: #6b7280; text-align: center;">
                <p style="margin: 8px 0;">
                    <strong>SpotStay</strong> — Gestión inteligente de propiedades
                </p>
                <p style="margin: 8px 0;">
                    Recibiste este correo porque alguien te contactó respecto a una incidencia en SpotStay.
                </p>
                <p style="margin: 8px 0; opacity: 0.8;">
                    © 2026 SpotStay. Todos los derechos reservados.
                </p>
            </div>

        </div>

    </div>

</body>
</html>
