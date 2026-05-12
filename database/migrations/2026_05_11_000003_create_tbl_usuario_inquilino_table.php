<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla específica para usuarios con rol INQUILINO
     * Contiene campos únicos de inquilinos (referencias, documentos, preferencias)
     */
    public function up(): void
    {
        Schema::create('tbl_usuario_inquilino', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario_inquilino')->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            
            // Documentación e identificación
            $table->string('nif_inquilino', 20)->nullable();
            $table->string('documento_identidad_inquilino_ruta')->nullable();
            $table->boolean('verificado_identidad_inquilino')->default(false);
            $table->timestamp('verificado_identidad_inquilino_fecha')->nullable();
            
            // Información profesional
            $table->string('empresa_inquilino', 150)->nullable();
            $table->string('puesto_inquilino', 100)->nullable();
            $table->string('tipo_inquilino', 30)->nullable(); // "profesional", "estudiante", "otro"
            
            // Referencias y verificación
            $table->text('referencias_inquilino')->nullable(); // JSONable: referencias previas
            $table->integer('puntuacion_inquilino')->default(0);
            $table->integer('propiedades_alquiladas')->default(0);
            
            // Preferencias y datos
            $table->string('preferencias_ubicacion', 255)->nullable();
            $table->integer('presupuesto_maximo_inquilino')->nullable();
            $table->text('preferencias_adicionales_inquilino')->nullable();
            
            // Estado
            $table->boolean('verificado_perfil_inquilino')->default(false);
            $table->integer('incidencias_abiertas')->default(0);
            
            // Timestamps
            $table->timestamp('creado_inquilino')->useCurrent();
            $table->timestamp('actualizado_inquilino')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign key e índices
            $table->foreign('id_usuario_fk')
                  ->references('id_usuario')
                  ->on('tbl_usuario')
                  ->onDelete('cascade');
            
            $table->index('nif_inquilino');
            $table->index('verificado_identidad_inquilino');
            $table->index('puntuacion_inquilino');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usuario_inquilino');
    }
};
