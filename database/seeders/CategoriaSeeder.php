<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre_categoria' => 'Cerrajería', 'descripcion_categoria' => 'Reparación y cambio de cerraduras'],
            ['nombre_categoria' => 'Fontanería', 'descripcion_categoria' => 'Problemas relacionados con tuberías, grifos y sistemas de agua'],
            ['nombre_categoria' => 'Humedades', 'descripcion_categoria' => 'Problemas de humedad, filtraciones y goteras'],
            ['nombre_categoria' => 'Calefacción', 'descripcion_categoria' => 'Reparación y mantenimiento de sistemas de calefacción'],
            ['nombre_categoria' => 'Climatización', 'descripcion_categoria' => 'Reparación y mantenimiento de sistemas de climatización'],
            ['nombre_categoria' => 'Electricidad', 'descripcion_categoria' => 'Problemas eléctricos, instalaciones y enchufes'],
            ['nombre_categoria' => 'Otro', 'descripcion_categoria' => 'Otras categorías no especificadas'],
        ];

        foreach ($categorias as $categoria) {
            DB::table('tbl_categoria')->insert([
                'nombre_categoria' => $categoria['nombre_categoria'],
                'descripcion_categoria' => $categoria['descripcion_categoria'],
                'estado_categoria' => 'activa',
                'creado_categoria' => now(),
                'actualizado_categoria' => now(),
            ]);
        }
    }
}
