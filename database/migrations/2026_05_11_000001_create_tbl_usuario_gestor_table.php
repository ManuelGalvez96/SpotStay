<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla específica para usuarios con rol GESTOR
     * Contiene campos únicos de gestores que no aplican a otros roles
     */
    public function up(): void
    {
        Schema::create('tbl_usuario_gestor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario_gestor')->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            
            // Campos específicos de gestor
            $table->string('nif_gestor', 20)->nullable();
            $table->string('empresa_gestor', 150)->nullable();
            $table->text('descripcion_gestor')->nullable();
            $table->string('especialidades_gestor', 255)->nullable(); // ej: "fontanería,electricidad"
            $table->integer('propiedades_gestionadas')->default(0);
            $table->decimal('calificacion_gestor', 3, 2)->nullable();
            $table->integer('tareas_completadas')->default(0);
            
            // Timestamps
            $table->timestamp('creado_gestor')->useCurrent();
            $table->timestamp('actualizado_gestor')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign key y índices
            $table->foreign('id_usuario_fk')
                  ->references('id_usuario')
                  ->on('tbl_usuario')
                  ->onDelete('cascade');
            
            $table->index('nif_gestor');
            $table->index('empresa_gestor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usuario_gestor');
    }
};
