<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de solicitudes de arrendador: registro de aplicaciones para ser arrendador
     */
    public function up(): void
    {
        Schema::create('tbl_solicitud_arrendador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_solicitud_arrendador')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->unsignedBigInteger('id_admin_revisa_fk')->nullable();
            $table->json('datos_solicitud_arrendador');
            $table->string('estado_solicitud_arrendador', 30)->default('pendiente');
            $table->text('notas_solicitud_arrendador')->nullable();
            $table->timestamp('creado_solicitud_arrendador')->nullable();
            $table->timestamp('actualizado_solicitud_arrendador')->nullable();

            // Foreign keys
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
            $table->foreign('id_admin_revisa_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_arrendador');
    }
};
