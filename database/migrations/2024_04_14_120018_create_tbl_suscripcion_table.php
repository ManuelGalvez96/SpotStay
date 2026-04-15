<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de suscripciones: planes de usuarios para limitar propiedades
     */
    public function up(): void
    {
        Schema::create('tbl_suscripcion', function (Blueprint $table) {
            $table->unsignedBigInteger('id_suscripcion')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->string('plan_suscripcion', 30);
            $table->unsignedTinyInteger('max_propiedades_suscripcion')->default(1);
            $table->date('inicio_suscripcion');
            $table->date('fin_suscripcion')->nullable();
            $table->string('estado_suscripcion', 20)->default('activa');
            $table->timestamp('creado_suscripcion')->nullable();
            $table->timestamp('actualizado_suscripcion')->nullable();

            // Foreign keys
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_suscripcion');
    }
};
