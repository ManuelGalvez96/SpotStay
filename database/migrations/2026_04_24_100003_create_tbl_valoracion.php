<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crear tabla tbl_valoracion
     * NOTA: Solo estructura. No implementar lógica en esta versión.
     */
    public function up(): void
    {
        Schema::create('tbl_valoracion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_valoracion')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_alquiler_fk');
            $table->unsignedBigInteger('id_autor_fk');
            $table->unsignedBigInteger('id_destinatario_fk')->nullable();
            $table->unsignedBigInteger('id_propiedad_fk')->nullable();
            $table->string('tipo_valoracion', 40);
            $table->unsignedTinyInteger('puntuacion_valoracion');
            $table->text('comentario_valoracion')->nullable();
            $table->timestamp('creado_valoracion')->useCurrent();

            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')
                ->on('tbl_alquiler')
                ->onDelete('restrict');
            $table->foreign('id_autor_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('restrict');
            $table->foreign('id_destinatario_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('set null');
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')
                ->on('tbl_propiedad')
                ->onDelete('set null');

            $table->unique(['id_alquiler_fk', 'id_autor_fk', 'tipo_valoracion']);
            $table->index('id_propiedad_fk');
            $table->index('id_destinatario_fk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_valoracion');
    }
};
