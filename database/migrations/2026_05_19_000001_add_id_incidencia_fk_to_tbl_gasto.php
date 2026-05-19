<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Añade la FK de incidencia a la tabla de gastos.
     * Permite trazar qué gasto fue generado automáticamente
     * al aprobar el presupuesto de una incidencia.
     */
    public function up(): void
    {
        Schema::table('tbl_gasto', function (Blueprint $table) {
            // Columna nullable: no todos los gastos vienen de una incidencia
            $table->unsignedBigInteger('id_incidencia_fk')
                ->nullable()
                ->after('id_alquiler_fk');

            $table->foreign('id_incidencia_fk')
                ->references('id_incidencia')
                ->on('tbl_incidencia')
                ->onDelete('set null'); // Si se borra la incidencia, el gasto se conserva sin enlace
        });
    }

    public function down(): void
    {
        Schema::table('tbl_gasto', function (Blueprint $table) {
            $table->dropForeign(['id_incidencia_fk']);
            $table->dropColumn('id_incidencia_fk');
        });
    }
};
