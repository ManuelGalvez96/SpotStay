<?php

namespace Database\Seeders;

use App\Models\Contrato;
use App\Models\Alquiler;
use Illuminate\Database\Seeder;

class ContratoSeeder extends Seeder
{
    public function run(): void
    {
        $alquileres = Alquiler::all();
        
        foreach ($alquileres as $index => $alquiler) {
            // Genera un PDF simulado (en producción sería un PDF real)
            $urlPdf = '/contratos/contrato_' . $alquiler->id_alquiler . '.pdf';
            $hashContrato = hash('sha256', json_encode([
                'id_alquiler' => $alquiler->id_alquiler,
                'timestamp' => now()->timestamp,
                'random' => rand(1000, 9999)
            ]));
            
            Contrato::firstOrCreate(
                ['id_alquiler_fk' => $alquiler->id_alquiler],
                [
                    'url_pdf_contrato' => $urlPdf,
                    'hash_contrato' => $hashContrato,
                    'firmado_arrendador' => (bool) rand(0, 1),
                    'fecha_firma_arrendador' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                    'ip_firma_arrendador' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                    'firmado_inquilino' => (bool) rand(0, 1),
                    'fecha_firma_inquilino' => rand(0, 1) ? now()->subDays(rand(1, 10)) : null,
                    'ip_firma_inquilino' => '192.168.' . rand(1, 255) . '.' . rand(1, 255),
                    'estado_contrato' => $index % 3 === 0 ? 'firmado' : 'pendiente',
                    'creado_contrato' => now()->subDays(rand(5, 30)),
                    'actualizado_contrato' => now(),
                ]
            );
        }
    }
}