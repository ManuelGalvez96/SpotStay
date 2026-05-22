<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_solicitud_alquiler', function (Blueprint $table) {
            $table->unsignedBigInteger('id_solicitud_alquiler')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_usuario_fk');
            $table->date('fecha_inicio_solicitud_alquiler');
            $table->text('mensaje_solicitud_alquiler');
            $table->string('estado_solicitud_alquiler', 30)->default('pendiente');
            $table->timestamp('creado_solicitud_alquiler')->nullable();
            $table->timestamp('actualizado_solicitud_alquiler')->nullable();

            $table->index('id_propiedad_fk');
            $table->index('id_usuario_fk');
            $table->index('estado_solicitud_alquiler');

            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('restrict');
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_alquiler');
    }
};
