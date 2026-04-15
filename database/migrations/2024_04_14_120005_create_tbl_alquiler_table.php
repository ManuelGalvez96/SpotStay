<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de alquileres: vincula propiedades con inquilinos
     */
    public function up(): void
    {
        Schema::create('tbl_alquiler', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alquiler')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_inquilino_fk');
            $table->unsignedBigInteger('id_admin_aprueba_fk')->nullable();
            $table->date('fecha_inicio_alquiler');
            $table->date('fecha_fin_alquiler')->nullable();
            $table->string('estado_alquiler', 30)->default('pendiente');
            $table->timestamp('aprobado_alquiler')->nullable();
            $table->timestamp('creado_alquiler')->nullable();
            $table->timestamp('actualizado_alquiler')->nullable();

            // Índices
            $table->index('id_propiedad_fk');
            $table->index('id_inquilino_fk');
            $table->index('estado_alquiler');

            // Foreign keys
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('restrict');
            $table->foreign('id_inquilino_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
            $table->foreign('id_admin_aprueba_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_alquiler');
    }
};
