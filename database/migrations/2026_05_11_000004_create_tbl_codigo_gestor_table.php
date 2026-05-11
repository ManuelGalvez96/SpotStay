<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de códigos únicos de gestor
     * Cada gestor tiene UN código permanente que los arrendadores usan para asignarlo
     * El código nunca caduca pero cada uso queda registrado en tbl_asignacion_gestor
     */
    public function up(): void
    {
        Schema::create('tbl_codigo_gestor', function (Blueprint $table) {
            $table->unsignedBigInteger('id_codigo_gestor')->primary()->autoIncrement();
            $table->string('codigo_gestor', 15)->unique(); // Ej: "GES-A7K9-M2Q5"
            $table->unsignedBigInteger('id_gestor_fk');
            $table->enum('estado_codigo_gestor', ['activo', 'cancelado'])->default('activo');
            
            // Auditoría
            $table->timestamp('creado_codigo_gestor')->useCurrent();
            $table->timestamp('actualizado_codigo_gestor')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('cancelado_codigo_gestor')->nullable();
            
            // Foreign key e índices
            $table->foreign('id_gestor_fk')
                  ->references('id_usuario_gestor')
                  ->on('tbl_usuario_gestor')
                  ->onDelete('cascade');
            
            $table->index('codigo_gestor');
            $table->index('estado_codigo_gestor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_codigo_gestor');
    }
};
