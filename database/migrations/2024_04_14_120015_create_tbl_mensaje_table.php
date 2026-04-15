<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de mensajes en conversaciones
     */
    public function up(): void
    {
        Schema::create('tbl_mensaje', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mensaje')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_conversacion_fk');
            $table->unsignedBigInteger('id_remitente_fk');
            $table->text('cuerpo_mensaje');
            $table->boolean('leido_mensaje')->default(false);
            $table->timestamp('creado_mensaje')->nullable();
            $table->timestamp('actualizado_mensaje')->nullable();

            // Índices
            $table->index('id_conversacion_fk');

            // Foreign keys
            $table->foreign('id_conversacion_fk')
                ->references('id_conversacion')->on('tbl_conversacion')
                ->onDelete('cascade');
            $table->foreign('id_remitente_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_mensaje');
    }
};
