<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crear tabla tbl_favorito
     * NOTA: Solo estructura. No implementar lógica en esta versión.
     */
    public function up(): void
    {
        Schema::create('tbl_favorito', function (Blueprint $table) {
            $table->unsignedBigInteger('id_favorito')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->timestamp('creado_favorito')->useCurrent();

            $table->foreign('id_usuario_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('cascade');
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')
                ->on('tbl_propiedad')
                ->onDelete('cascade');

            $table->unique(['id_usuario_fk', 'id_propiedad_fk']);
            $table->index('id_propiedad_fk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_favorito');
    }
};
