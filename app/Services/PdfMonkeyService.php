<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PdfMonkeyService
{
    public function currentUser(): array
    {
        return $this->request()
            ->get('/current_user')
            ->throw()
            ->json();
    }

    public function createDocument(array $payload, array $meta = [], ?string $templateId = null, ?string $status = null): array
    {
        $documentTemplateId = $templateId ?: (string) config('pdfmonkey.template_id');
        $documentStatus = $status ?: (string) config('pdfmonkey.default_status', 'pending');

        $response = $this->request()->post('/documents', [
            'document' => [
                'document_template_id' => $documentTemplateId,
                'status' => $documentStatus,
                'payload' => $payload,
                'meta' => $meta,
            ],
        ]);

        $response->throw();

        return $response->json();
    }

    public function createDocumentSync(array $payload, array $meta = [], ?string $templateId = null): array
    {
        $documentTemplateId = $templateId ?: (string) config('pdfmonkey.template_id');

        $response = $this->request()->post('/documents/sync', [
            'document' => [
                'document_template_id' => $documentTemplateId,
                'status' => 'pending',
                'payload' => $payload,
                'meta' => $meta,
            ],
        ]);

        $response->throw();

        return $response->json();
    }

    public function getDocumentCard(string $documentId): array
    {
        $response = $this->request()->get('/document_cards/' . $documentId);
        $response->throw();

        return $response->json();
    }

    public function getDocument(string $documentId): array
    {
        $response = $this->request()->get('/documents/' . $documentId);
        $response->throw();

        return $response->json();
    }

    public function downloadUrl(string $documentId): ?string
    {
        $documentCard = $this->getDocumentCard($documentId);

        return $documentCard['document_card']['download_url'] ?? null;
    }

    public function previewUrl(string $documentId): ?string
    {
        $documentCard = $this->getDocumentCard($documentId);

        return $documentCard['document_card']['preview_url'] ?? null;
    }

    public function isConfigured(): bool
    {
        return filled(config('pdfmonkey.api_key')) && filled(config('pdfmonkey.template_id'));
    }

    public function buildMeta(array $parts = [], ?string $filename = null): array
    {
        $meta = $parts;

        if ($filename) {
            $meta['_filename'] = $filename;
        }

        return $meta;
    }

    public function buildFilename(string $baseName): string
    {
        $prefix = trim((string) config('pdfmonkey.default_filename_prefix', 'spotstay'));
        $sanitized = preg_replace('/[^A-Za-z0-9\-_. ]+/', '', $baseName) ?: 'documento';

        return trim($prefix . ' ' . $sanitized) . '.pdf';
    }

    private function request()
    {
        return Http::baseUrl((string) config('pdfmonkey.base_url'))
            ->acceptJson()
            ->asJson()
            ->withToken((string) config('pdfmonkey.api_key'))
            ->timeout((int) config('pdfmonkey.timeout', 30))
            ->connectTimeout((int) config('pdfmonkey.connect_timeout', 10));
    }
}
