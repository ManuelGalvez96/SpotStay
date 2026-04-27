<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crear tabla tbl_visita
     * NOTA: Solo estructura. No implementar lógica en esta versión.
     * Para futuro registro de visitas a propiedades (sin lógica adicional)
     */
    public function up(): void
    {
        Schema::create('tbl_visita', function (Blueprint $table) {
            $table->unsignedBigInteger('id_visita')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_usuario_fk')->nullable();
            $table->string('ip_visita', 45)->nullable();
            $table->string('sesion_visita', 100)->nullable();
            $table->timestamp('creado_visita')->useCurrent();

            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')
                ->on('tbl_propiedad')
                ->onDelete('cascade');
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('set null');

            $table->index(['id_propiedad_fk', 'creado_visita']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_visita');
    }
};
