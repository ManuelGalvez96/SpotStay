<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla pivote: participantes en una conversación
     */
    public function up(): void
    {
        Schema::create('tbl_conversacion_usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_conversacion_usuario')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_conversacion_fk');
            $table->unsignedBigInteger('id_usuario_fk');
            $table->timestamp('ultima_lectura_conv_usuario')->nullable();

            // Índices y restricciones
            $table->unique(['id_conversacion_fk', 'id_usuario_fk']);
            $table->foreign('id_conversacion_fk')
                ->references('id_conversacion')->on('tbl_conversacion')
                ->onDelete('cascade');
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_conversacion_usuario');
    }
};
