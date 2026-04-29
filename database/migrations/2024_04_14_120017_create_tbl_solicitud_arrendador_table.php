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
            $table->string('telefono_solicitud', 20)->nullable();
            $table->date('fecha_nacimiento_solicitud')->nullable();
            $table->string('tipo_documento_solicitud', 10)->nullable();
            $table->string('numero_documento_solicitud', 20)->nullable();
            $table->string('iban_solicitud', 34)->nullable();
            $table->string('titular_cuenta_solicitud', 100)->nullable();
            $table->string('nif_solicitud', 20)->nullable();
            $table->string('direccion_fiscal_solicitud', 255)->nullable();
            $table->string('tipo_arrendador_solicitud', 20)->nullable();
            $table->text('descripcion_solicitud')->nullable();
            $table->unsignedTinyInteger('num_propiedades_previstas_solicitud')->nullable();
            $table->boolean('es_propietario_solicitud')->default(false);
            $table->boolean('acepta_terminos_solicitud')->default(false);
            $table->boolean('acepta_veracidad_solicitud')->default(false);
            $table->timestamp('fecha_aceptacion_solicitud')->nullable();
            $table->string('estado_solicitud_arrendador', 30)->default('pendiente');
            $table->timestamp('revisado_solicitud_arrendador')->nullable();
            $table->text('notas_solicitud_arrendador')->nullable();
            $table->timestamp('creado_solicitud_arrendador')->nullable();
            $table->timestamp('actualizado_solicitud_arrendador')->nullable();

            // Índices
            $table->index('id_usuario_fk');
            $table->index('estado_solicitud_arrendador');
            $table->index('id_admin_revisa_fk');

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
