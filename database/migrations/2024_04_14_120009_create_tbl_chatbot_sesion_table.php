<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de sesiones del chatbot IA
     */
    public function up(): void
    {
        Schema::create('tbl_chatbot_sesion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sesion_chatbot')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->timestamp('creado_sesion_chatbot')->nullable();
            $table->timestamp('actualizado_sesion_chatbot')->nullable();

            // Índices
            $table->index(['id_usuario_fk', 'creado_sesion_chatbot']);

            // Foreign keys
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_chatbot_sesion');
    }
};
