<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de conversaciones: chats entre usuarios
     */
    public function up(): void
    {
        Schema::create('tbl_conversacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_conversacion')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk')->nullable();
            $table->string('tipo_conversacion', 30)->default('directa');
            $table->timestamp('creado_conversacion')->nullable();
            $table->timestamp('actualizado_conversacion')->nullable();

            // Foreign keys
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_conversacion');
    }
};
