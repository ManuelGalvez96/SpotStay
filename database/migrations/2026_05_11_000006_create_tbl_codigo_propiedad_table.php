<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de códigos de invitación para propiedades
     * Los arrendadores generan códigos únicos que inquilinos usan para ver/solicitar alquiler
     * Los códigos tienen validez de 30 días desde su creación
     */
    public function up(): void
    {
        Schema::create('tbl_codigo_propiedad', function (Blueprint $table) {
            $table->unsignedBigInteger('id_codigo_propiedad')->primary()->autoIncrement();
            $table->string('codigo_propiedad', 15)->unique(); // Ej: "PROP-B3M8-K7X2"
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_arrendador_genero_fk'); // Quién creó el código
            
            // Control de validez
            $table->enum('estado_codigo_propiedad', ['activo', 'usado', 'expirado', 'cancelado'])->default('activo');
            $table->integer('dias_validez_codigo')->default(30);
            $table->timestamp('expira_codigo_propiedad')->nullable();
            
            // Auditoría de uso
            $table->unsignedBigInteger('id_inquilino_uso_fk')->nullable(); // Quién lo usó
            $table->timestamp('usado_codigo_propiedad')->nullable();
            $table->integer('usos_codigo')->default(0); // Contador de intentos
            
            // Timestamps
            $table->timestamp('creado_codigo_propiedad')->useCurrent();
            $table->timestamp('actualizado_codigo_propiedad')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign keys e índices
            $table->foreign('id_propiedad_fk')
                  ->references('id_propiedad')
                  ->on('tbl_propiedad')
                  ->onDelete('cascade');
            
            $table->foreign('id_arrendador_genero_fk')
                  ->references('id_usuario_arrendador')
                  ->on('tbl_usuario_arrendador')
                  ->onDelete('restrict');
            
            $table->foreign('id_inquilino_uso_fk')
                  ->references('id_usuario_inquilino')
                  ->on('tbl_usuario_inquilino')
                  ->onDelete('set null');
            
            $table->index('codigo_propiedad');
            $table->index('id_propiedad_fk');
            $table->index('estado_codigo_propiedad');
            $table->index('expira_codigo_propiedad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_codigo_propiedad');
    }
};
