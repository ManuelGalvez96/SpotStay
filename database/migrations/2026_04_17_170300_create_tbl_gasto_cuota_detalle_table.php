<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_gasto_cuota_detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('id_gasto_cuota_detalle')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_gasto_cuota_fk');
            $table->unsignedBigInteger('id_alquiler_fk');
            $table->unsignedBigInteger('id_pagador_fk');
            $table->decimal('importe_detalle', 10, 2);
            $table->string('estado_detalle', 30)->default('pendiente');
            $table->timestamp('pagado_detalle')->nullable();
            $table->timestamp('creado_detalle')->nullable();
            $table->timestamp('actualizado_detalle')->nullable();

            $table->index('id_gasto_cuota_fk');
            $table->index('id_alquiler_fk');
            $table->index('id_pagador_fk');
            $table->index('estado_detalle');
            $table->unique(['id_gasto_cuota_fk', 'id_alquiler_fk'], 'uq_cuota_alquiler');

            $table->foreign('id_gasto_cuota_fk')
                ->references('id_gasto_cuota')->on('tbl_gasto_cuota')
                ->onDelete('cascade');

            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')->on('tbl_alquiler')
                ->onDelete('cascade');

            $table->foreign('id_pagador_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_gasto_cuota_detalle');
    }
};
