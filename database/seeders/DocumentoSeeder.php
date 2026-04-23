<?php

namespace Database\Seeders;

use App\Models\Documento;
use App\Models\Usuario;
use App\Models\Alquiler;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $documentos = [
            ['tipo' => 'contrato', 'subtipo' => 'alquiler', 'nombre' => 'Contrato de Alquiler'],
            ['tipo' => 'certificado', 'subtipo' => 'identidad', 'nombre' => 'Documento de Identidad'],
            ['tipo' => 'solicitud', 'subtipo' => 'alquiler', 'nombre' => 'Solicitud de Alquiler'],
            ['tipo' => 'seguro', 'subtipo' => 'propiedad', 'nombre' => 'Póliza de Seguros'],
            ['tipo' => 'banco', 'subtipo' => 'autorizacion', 'nombre' => 'Autorización Bancaria'],
            ['tipo' => 'factura', 'subtipo' => 'servicios', 'nombre' => 'Factura de Servicios'],
            ['tipo' => 'recibo', 'subtipo' => 'pago', 'nombre' => 'Recibo de Pago'],
            ['tipo' => 'inventario', 'subtipo' => 'ingreso', 'nombre' => 'Inventario de Entrada'],
            ['tipo' => 'reporte', 'subtipo' => 'inspeccion', 'nombre' => 'Reporte de Inspección'],
            ['tipo' => 'anexo', 'subtipo' => 'contrato', 'nombre' => 'Anexos al Contrato'],
            ['tipo' => 'contrato', 'subtipo' => 'alquiler', 'nombre' => 'Contrato Adicional'],
            ['tipo' => 'certificado', 'subtipo' => 'ingresos', 'nombre' => 'Certificado de Ingresos'],
            ['tipo' => 'declaracion', 'subtipo' => 'impuestos', 'nombre' => 'Declaración de Impuestos'],
            ['tipo' => 'deposito', 'subtipo' => 'garantia', 'nombre' => 'Recibo de Depósito'],
            ['tipo' => 'contrato', 'subtipo' => 'servicios', 'nombre' => 'Contrato de Servicios'],
            ['tipo' => 'factura', 'subtipo' => 'mantenimiento', 'nombre' => 'Factura de Mantenimiento'],
            ['tipo' => 'solicitud', 'subtipo' => 'reparacion', 'nombre' => 'Solicitud de Reparación'],
            ['tipo' => 'reporte', 'subtipo' => 'danos', 'nombre' => 'Reporte de Daños'],
            ['tipo' => 'inventario', 'subtipo' => 'salida', 'nombre' => 'Inventario de Salida'],
            ['tipo' => 'certificado', 'subtipo' => 'desocupacion', 'nombre' => 'Certificado de Desocupación'],
        ];

        $usuarios = Usuario::all();
        $alquileres = Alquiler::all();

        foreach ($documentos as $index => $data) {
            $usuario = $usuarios->random();
            $alquiler = $alquileres->isNotEmpty() ? $alquileres->random() : null;
            
            // Genera hash único para el documento
            $hash = hash('sha256', json_encode([
                'usuario' => $usuario->id_usuario,
                'timestamp' => now()->timestamp,
                'random' => rand(1000, 9999)
            ]));
            
            $url = '/documentos/' . strtolower($data['tipo']) . '_' . ($index + 1) . '.pdf';

            Documento::create([
                'id_usuario_fk' => $usuario->id_usuario,
                'tipo_documento' => $data['tipo'],
                'tipo_entidad_documento' => 'alquiler',
                'id_entidad_documento' => $alquiler?->id_alquiler ?? 1,
                'nombre_documento' => $data['nombre'],
                'url_documento' => $url,
                'hash_documento' => $hash,
                'pdfmonkey_id_documento' => null,
                'creado_documento' => now()->subDays(rand(1, 30)),
                'actualizado_documento' => now(),
            ]);
        }
    }
}
