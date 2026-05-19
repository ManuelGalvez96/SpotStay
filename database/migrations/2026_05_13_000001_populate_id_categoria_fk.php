<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mapeo de nombres string a categorías en la BD
        $mapeo = [
            'fontaneria' => 'Fontanería',
            'electricidad' => 'Electricidad',
            'calefaccion' => 'Calefacción',
            'climatizacion' => 'Climatización',
            'humedades' => 'Humedades',
            'cerrajeria' => 'Cerrajería',
            'otro' => 'Otro',
        ];

        foreach ($mapeo as $nombreString => $nombreCategoria) {
            $idCategoria = DB::table('tbl_categoria')
                ->where('nombre_categoria', $nombreCategoria)
                ->value('id_categoria');

            if ($idCategoria) {
                DB::table('tbl_incidencia')
                    ->where('categoria_incidencia', $nombreString)
                    ->update(['id_categoria_fk' => $idCategoria]);
            }
        }
    }

    public function down(): void
    {
        DB::table('tbl_incidencia')
            ->whereNotNull('id_categoria_fk')
            ->update(['id_categoria_fk' => null]);
    }
};
