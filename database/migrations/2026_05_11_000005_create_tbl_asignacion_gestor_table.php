<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de asignación de gestores a propiedades
     * Crea un audit trail de quién asignó qué gestor a cuál propiedad y cuándo
     * Permite historial completo de gestores por propiedad
     */
    public function up(): void
    {
        Schema::create('tbl_asignacion_gestor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_asignacion_gestor')->primary()->autoIncrement();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_gestor_fk');
            $table->unsignedBigInteger('id_arrendador_asigno_fk'); // Usuario arrendador que hizo la asignación
            
            // Estado y notas
            $table->enum('estado_asignacion', ['activa', 'reemplazada', 'cancelada'])->default('activa');
            $table->text('notas_asignacion')->nullable();
            
            // Fechas importantes
            $table->timestamp('asignado_gestor')->useCurrent();
            $table->timestamp('inicio_gestion')->nullable();
            $table->timestamp('fin_gestion')->nullable(); // Cuando fue reemplazado o cancelado
            
            // Auditoría
            $table->timestamp('creado_asignacion')->useCurrent();
            $table->timestamp('actualizado_asignacion')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign keys e índices
            $table->foreign('id_propiedad_fk')
                  ->references('id_propiedad')
                  ->on('tbl_propiedad')
                  ->onDelete('cascade');
            
            $table->foreign('id_gestor_fk')
                  ->references('id_usuario_gestor')
                  ->on('tbl_usuario_gestor')
                  ->onDelete('restrict');
            
            $table->foreign('id_arrendador_asigno_fk')
                  ->references('id_usuario_arrendador')
                  ->on('tbl_usuario_arrendador')
                  ->onDelete('restrict');
            
            $table->index('id_propiedad_fk');
            $table->index('id_gestor_fk');
            $table->index('estado_asignacion');
            $table->index('asignado_gestor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_asignacion_gestor');
    }
};
