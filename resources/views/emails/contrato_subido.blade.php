<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpotStay - Contrato subido</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f9fafb;">

    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">

        <div style="background: linear-gradient(135deg, #1AA068 0%, #15824a 100%); padding: 30px 20px; text-align: center; color: white;">
            <div style="font-size: 24px; font-weight: bold; margin-bottom: 6px;">SpotStay</div>
            <div style="font-size: 13px; opacity: 0.95;">Documento de contrato disponible</div>
        </div>

        <div style="padding: 30px;">
            <p style="font-size: 16px; margin-bottom: 10px;">Hola <strong>{{ $nombreInquilino ?? 'inquilino' }}</strong>,</p>

            <div style="background-color: #f3f4f6; border-left: 4px solid #1AA068; padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 15px; line-height: 1.6;">
                El arrendador ha subido el PDF del contrato correspondiente a tu alquiler (ID: <strong>{{ $idAlquiler }}</strong>).
            </div>

            <div style="font-size: 14px; line-height: 1.6; color: #555; margin-bottom: 20px;">
                Puedes ver o descargar el contrato desde el enlace que aparece abajo. Guarda el documento si lo necesitas para futuras consultas.
            </div>

            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <div style="font-size: 12px; color: #6b7280; text-align: center;">
                <p style="margin: 6px 0;"><strong>SpotStay</strong> — Gestión inteligente de propiedades</p>
                <p style="margin: 6px 0;">Recibiste este correo porque eres parte del alquiler asociado. Si no esperabas este documento, contacta con tu arrendador o con soporte.</p>
                <p style="margin: 6px 0; opacity: 0.8;">© 2026 SpotStay. Todos los derechos reservados.</p>
            </div>
        </div>

    </div>

</body>
</html>
