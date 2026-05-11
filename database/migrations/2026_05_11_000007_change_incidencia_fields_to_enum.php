<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Cambio de columnas estado_incidencia y prioridad_incidencia a ENUM
     * Proporciona validación a nivel de base de datos y mejora integridad
     */
    public function up(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            // Cambiar estado_incidencia a ENUM
            $table->enum('estado_incidencia', [
                'abierta',
                'esperando_decision',
                'esperando_pago',
                'solucionada',
                'resuelta'
            ])->default('abierta')->change();
            
            // Cambiar prioridad_incidencia a ENUM
            $table->enum('prioridad_incidencia', [
                'baja',
                'media',
                'alta',
                'urgente'
            ])->default('media')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            // Revertir a VARCHAR
            $table->string('estado_incidencia', 50)->default('abierta')->change();
            $table->string('prioridad_incidencia', 50)->default('media')->change();
        });
    }
};
