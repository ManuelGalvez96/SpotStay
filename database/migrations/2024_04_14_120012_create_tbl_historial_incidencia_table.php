<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de histórico de incidencias: registra cambios y comentarios
     */
    public function up(): void
    {
        Schema::create('tbl_historial_incidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_historial_incidencia')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_incidencia_fk');
            $table->unsignedBigInteger('id_usuario_fk');
            $table->text('comentario_historial')->nullable();
            $table->string('cambio_estado_historial', 30)->nullable();
            $table->timestamp('creado_historial')->nullable();
            $table->timestamp('actualizado_historial')->nullable();

            // Foreign keys
            $table->foreign('id_incidencia_fk')
                ->references('id_incidencia')->on('tbl_incidencia')
                ->onDelete('cascade');
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_historial_incidencia');
    }
};
