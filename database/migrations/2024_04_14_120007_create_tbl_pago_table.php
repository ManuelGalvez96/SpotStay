<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de pagos: registra pagos de alquileres (rentas, servicios, depósitos)
     */
    public function up(): void
    {
        Schema::create('tbl_pago', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pago')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_alquiler_fk');
            $table->unsignedBigInteger('id_pagador_fk');
            $table->unsignedBigInteger('id_gasto_cuota_detalle_fk')->nullable();
            $table->unsignedBigInteger('id_gasto_cuota_fk')->nullable();
            $table->enum('tipo_pago', ['alquiler', 'gasto', 'fianza']);
            $table->string('concepto_pago', 200);
            $table->decimal('importe_pago', 8, 2);
            $table->date('mes_pago')->nullable();
            $table->string('estado_pago', 30)->default('pendiente');
            $table->string('referencia_pago', 100)->nullable();
            $table->timestamp('fecha_confirmacion_pago')->nullable();
            $table->timestamp('creado_pago')->nullable();
            $table->timestamp('actualizado_pago')->nullable();

            // Índices
            $table->index('id_alquiler_fk');
            $table->index('id_pagador_fk');
            $table->index('id_gasto_cuota_detalle_fk');
            $table->index('id_gasto_cuota_fk');
            $table->index('estado_pago');
            $table->index('mes_pago');
            $table->index('referencia_pago');

            // Foreign keys
            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')->on('tbl_alquiler')
                ->onDelete('restrict');
            $table->foreign('id_pagador_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pago');
    }
};
