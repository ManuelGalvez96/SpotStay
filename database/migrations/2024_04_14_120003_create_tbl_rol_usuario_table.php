<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla pivote: asignación de roles a usuarios
     */
    public function up(): void
    {
        Schema::create('tbl_rol_usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol_usuario')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->unsignedBigInteger('id_rol_fk');
            $table->timestamp('asignado_rol_usuario')->useCurrent();

            // Índices y restricciones
            $table->unique(['id_usuario_fk', 'id_rol_fk']);
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
            $table->foreign('id_rol_fk')
                ->references('id_rol')->on('tbl_rol')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_rol_usuario');
    }
};
