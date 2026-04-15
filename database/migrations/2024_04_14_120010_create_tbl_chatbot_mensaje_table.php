<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de mensajes del chatbot: historial de conversaciones
     */
    public function up(): void
    {
        Schema::create('tbl_chatbot_mensaje', function (Blueprint $table) {
            $table->unsignedBigInteger('id_mensaje_chatbot')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_sesion_chatbot_fk');
            $table->string('rol_mensaje_chatbot', 10);
            $table->text('cuerpo_mensaje_chatbot');
            $table->timestamp('creado_mensaje_chatbot')->nullable();

            // Índices
            $table->index('id_sesion_chatbot_fk');

            // Foreign keys
            $table->foreign('id_sesion_chatbot_fk')
                ->references('id_sesion_chatbot')->on('tbl_chatbot_sesion')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_chatbot_mensaje');
    }
};
