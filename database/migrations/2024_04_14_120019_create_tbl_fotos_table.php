<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de fotos de propiedades
     */
    public function up(): void
    {
        Schema::create('tbl_fotos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_foto')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->string('ruta_foto', 255);
            $table->integer('orden')->unsigned()->default(0);
            $table->timestamp('creado_foto')->nullable();

            // Indices y restricciones
            $table->index('id_propiedad_fk');
            $table->unique(['id_propiedad_fk', 'ruta_foto']);

            // Foreign keys
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_fotos');
    }
};
