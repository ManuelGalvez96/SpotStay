<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crear tabla tbl_historial_propiedad
     * Tabla de auditoría para registrar cambios en propiedades
     */
    public function up(): void
    {
        Schema::create('tbl_historial_propiedad', function (Blueprint $table) {
            $table->unsignedBigInteger('id_historial_propiedad')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_usuario_fk');
            $table->string('tipo_cambio_historial', 50);
            $table->string('campo_modificado_historial', 100)->nullable();
            $table->text('valor_anterior_historial')->nullable();
            $table->text('valor_nuevo_historial')->nullable();
            $table->string('estado_anterior_historial', 30)->nullable();
            $table->string('estado_nuevo_historial', 30)->nullable();
            $table->text('comentario_historial')->nullable();
            $table->timestamp('creado_historial')->useCurrent();

            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')
                ->on('tbl_propiedad')
                ->onDelete('cascade');
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('restrict');

            $table->index('id_propiedad_fk');
            $table->index('creado_historial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_historial_propiedad');
    }
};
