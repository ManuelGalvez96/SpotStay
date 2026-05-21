<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_solicitud_gestor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_solicitud_gestor')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->unsignedBigInteger('id_admin_revisa_fk')->nullable();

            $table->text('descripcion_solicitud')->nullable();
            $table->text('experiencia_solicitud')->nullable();
            $table->boolean('acepta_terminos_solicitud')->default(false);
            $table->boolean('acepta_veracidad_solicitud')->default(false);
            $table->date('fecha_aceptacion_solicitud')->nullable();

            $table->string('estado_solicitud_gestor', 20)->default('pendiente');
            $table->text('notas_solicitud_gestor')->nullable();

            $table->timestamp('creado_solicitud_gestor')->nullable();
            $table->timestamp('actualizado_solicitud_gestor')->nullable();

            $table->foreign('id_usuario_fk')->references('id_usuario')->on('tbl_usuario');
            $table->foreign('id_admin_revisa_fk')->references('id_usuario')->on('tbl_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_gestor');
    }
};
