<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PdfMonkeyService
{
    public function obtenerUsuarioActual(): array
    {
        return $this->solicitud()
            ->get('/current_user')
            ->throw()
            ->json();
    }

    public function crearDocumento(array $datos, array $meta = [], ?string $idPlantilla = null, ?string $estado = null): array
    {
        $idPlantillaDocumento = $idPlantilla ?: (string) config('pdfmonkey.template_id');
        $estadoDocumento = $estado ?: (string) config('pdfmonkey.default_status', 'pending');

        $respuesta = $this->solicitud()->post('/documents', [
            'document' => [
                'document_template_id' => $idPlantillaDocumento,
                'status' => $estadoDocumento,
                'payload' => $datos,
                'meta' => $meta,
            ],
        ]);

        $respuesta->throw();

        return $respuesta->json();
    }

    /**
     * CREA UN DOCUMENTO EN PDFMONKEY DE FORMA SINCRÓNICA.
     *
     * "Sincrónico" significa que la petición espera a que PDFMonkey termine
     * de generar el PDF antes de responder. Esto es ideal para cuando el usuario
     * está esperando la descarga en ese mismo momento.
     *
     * @param array $datos (Payload) Los datos que se inyectarán en la plantilla HTML (ej: nombre, precio).
     * @param array $meta Información adicional como el nombre del archivo.
     * @param string|null $idPlantilla Opcional: ID de la plantilla HTML a usar. Si es null, usa la del config.
     */
    public function crearDocumentoSincronizado(array $datos, array $meta = [], ?string $idPlantilla = null): array
    {
        $idPlantillaDocumento = $idPlantilla ?: (string) config('pdfmonkey.template_id');

        // Enviamos un POST a la ruta /documents/sync de la API
        $respuesta = $this->solicitud()->post('/documents/sync', [
            'document' => [
                'document_template_id' => $idPlantillaDocumento, // Qué diseño usar
                'status' => 'pending',                           // Estado inicial
                'payload' => $datos,                             // Los datos a rellenar
                'meta' => $meta,                                 // Nombre del archivo
            ],
        ]);

        // Si falla (4xx o 5xx), lanzará una excepción
        $respuesta->throw();
 
        // Devolvemos el JSON con la URL de descarga
        return $respuesta->json();
    }

    public function obtenerTarjetaDocumento(string $idDocumento): array
    {
        $respuesta = $this->solicitud()->get('/document_cards/' . $idDocumento);
        $respuesta->throw();

        return $respuesta->json();
    }

    public function obtenerDocumento(string $idDocumento): array
    {
        $respuesta = $this->solicitud()->get('/documents/' . $idDocumento);
        $respuesta->throw();

        return $respuesta->json();
    }

    public function obtenerUrlDescarga(string $idDocumento): ?string
    {
        $tarjetaDocumento = $this->obtenerTarjetaDocumento($idDocumento);

        return $tarjetaDocumento['document_card']['download_url'] ?? null;
    }

    public function obtenerUrlVista(string $idDocumento): ?string
    {
        $tarjetaDocumento = $this->obtenerTarjetaDocumento($idDocumento);

        return $tarjetaDocumento['document_card']['preview_url'] ?? null;
    }

    public function estaConfigurado(): bool
    {
        return filled(config('pdfmonkey.api_key')) && filled(config('pdfmonkey.template_id'));
    }

    public function construirMeta(array $partes = [], ?string $nombreArchivo = null): array
    {
        $meta = $partes;

        if ($nombreArchivo) {
            $meta['_filename'] = $nombreArchivo;
        }

        return $meta;
    }

    public function construirNombreArchivo(string $nombreBase): string
    {
        $prefijo = trim((string) config('pdfmonkey.default_filename_prefix', 'spotstay'));
        $sanitizado = preg_replace('/[^A-Za-z0-9\-_. ]+/', '', $nombreBase) ?: 'documento';

        return trim($prefijo . ' ' . $sanitizado) . '.pdf';
    }

    /**
     * CONFIGURA LA CONEXIÓN HTTP CON LA API DE PDFMONKEY.
     *
     * Este método privado prepara el cliente HTTP (Guzzle/Laravel Http Client) con:
     * 1. La URL base de la API (desde el config).
     * 2. El Token de Autenticación (API Key).
     * 3. Configuración de seguridad (SSL).
     * 4. Tiempos de espera (Timeouts).
     */
    private function solicitud()
    {
        $solicitud = Http::baseUrl((string) config('pdfmonkey.base_url'))
            ->acceptJson()             // Esperamos recibir JSON
            ->asJson()                 // Enviamos JSON
            ->withToken((string) config('pdfmonkey.api_key')) // <--- Aquí se pone la contraseña (API Key)
            ->timeout((int) config('pdfmonkey.timeout', 30))  // Tiempo máximo de espera
            ->connectTimeout((int) config('pdfmonkey.connect_timeout', 10));

        // En entorno local o si se configura, desactivamos la verificación SSL para evitar errores de certificados.
        if (!config('pdfmonkey.verify_ssl', true) || app()->environment('local')) {
            $solicitud = $solicitud->withoutVerifying();
        }

        return $solicitud;
    }
}
