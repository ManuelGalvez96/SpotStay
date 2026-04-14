<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de contratos de alquiler con información de firmas digitales
     */
    public function up(): void
    {
        Schema::create('tbl_contrato', function (Blueprint $table) {
            $table->unsignedBigInteger('id_contrato')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_alquiler_fk')->unique();
            $table->string('url_pdf_contrato', 500);
            $table->string('hash_contrato', 64);
            $table->boolean('firmado_arrendador')->default(false);
            $table->timestamp('fecha_firma_arrendador')->nullable();
            $table->string('ip_firma_arrendador', 45)->nullable();
            $table->boolean('firmado_inquilino')->default(false);
            $table->timestamp('fecha_firma_inquilino')->nullable();
            $table->string('ip_firma_inquilino', 45)->nullable();
            $table->string('estado_contrato', 30)->default('pendiente');
            $table->timestamp('creado_contrato')->nullable();
            $table->timestamp('actualizado_contrato')->nullable();

            // Índices
            $table->index('estado_contrato');

            // Foreign keys
            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')->on('tbl_alquiler')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_contrato');
    }
};
