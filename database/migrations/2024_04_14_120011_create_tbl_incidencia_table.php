<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de incidencias: reportes de problemas en propiedades
     */
    public function up(): void
    {
        Schema::create('tbl_incidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_incidencia')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_reporta_fk');
            $table->unsignedBigInteger('id_asignado_fk')->nullable();
            $table->string('titulo_incidencia', 200);
            $table->text('descripcion_incidencia');
            $table->string('categoria_incidencia', 50);
            $table->string('prioridad_incidencia', 20)->default('media');
            $table->string('estado_incidencia', 30)->default('abierta');
            $table->string('esperando_de_incidencia', 30)->nullable();
            $table->decimal('presupuesto_importe_incidencia', 10, 2)->nullable();
            $table->text('detalle_presupuesto_incidencia')->nullable();
            $table->string('responsable_pago_incidencia', 30)->nullable();
            $table->boolean('pagado_presupuesto_incidencia')->default(false);
            $table->timestamp('pagado_incidencia')->nullable();
            $table->timestamp('resuelto_incidencia')->nullable();
            $table->timestamp('cerrado_incidencia')->nullable();
            $table->timestamp('creado_incidencia')->nullable();
            $table->timestamp('actualizado_incidencia')->nullable();

            // Índices
            $table->index('id_propiedad_fk');
            $table->index('estado_incidencia');

            // Foreign keys
            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('restrict');
            $table->foreign('id_reporta_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
            $table->foreign('id_asignado_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_incidencia');
    }
};
