<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de notificaciones del sistema
     */
    public function up(): void
    {
        Schema::create('tbl_notificacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_notificacion')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->string('tipo_notificacion', 100);
            $table->json('datos_notificacion');
            $table->boolean('leida_notificacion')->default(false);
            $table->timestamp('leida_en_notificacion')->nullable();
            $table->timestamp('creado_notificacion')->nullable();
            $table->timestamp('actualizado_notificacion')->nullable();

            // Índices
            $table->index(['id_usuario_fk', 'leida_notificacion']);

            // Foreign keys
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_notificacion');
    }
};
