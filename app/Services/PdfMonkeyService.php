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

    public function crearDocumentoSincronizado(array $datos, array $meta = [], ?string $idPlantilla = null): array
    {
        $idPlantillaDocumento = $idPlantilla ?: (string) config('pdfmonkey.template_id');

        $respuesta = $this->solicitud()->post('/documents/sync', [
            'document' => [
                'document_template_id' => $idPlantillaDocumento,
                'status' => 'pending',
                'payload' => $datos,
                'meta' => $meta,
            ],
        ]);

        $respuesta->throw();
 
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

    private function solicitud()
    {
        return Http::baseUrl((string) config('pdfmonkey.base_url'))
            ->acceptJson()
            ->asJson()
            ->withToken((string) config('pdfmonkey.api_key'))
            ->timeout((int) config('pdfmonkey.timeout', 30))
            ->connectTimeout((int) config('pdfmonkey.connect_timeout', 10));
    }
}
