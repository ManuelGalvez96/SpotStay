<?php

namespace Database\Seeders;

use App\Models\Propiedad;
use App\Models\UsuarioArrendador;
use App\Services\CodigoPropiedadService;
use Illuminate\Database\Seeder;

class CodigoPropiedadSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener propiedades con arrendador
        $propiedades = Propiedad::whereNotNull('id_arrendador_fk')->get();
        
        foreach ($propiedades as $propiedad) {
            // Obtener el arrendador de esa propiedad
            $arrendador = UsuarioArrendador::where('id_usuario_fk', $propiedad->id_arrendador_fk)->first();
            
            if ($arrendador) {
                try {
                    // Crear 1 código activo por propiedad, válido por 30 días
                    CodigoPropiedadService::crearCodigoParaPropiedad(
                        $propiedad->id_propiedad,
                        $arrendador->id_usuario_arrendador,
                        30
                    );
                } catch (\Exception $e) {
                    // Si ya existe, continuar
                    continue;
                }
            }
        }
    }
}
