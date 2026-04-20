<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_gasto', function (Blueprint $table) {
            $table->unsignedBigInteger('id_gasto')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_alquiler_fk')->nullable();
            $table->unsignedBigInteger('id_gestor_fk');
            $table->string('concepto_gasto', 200);
            $table->string('categoria_gasto', 50)->nullable();
            $table->decimal('importe_gasto', 10, 2);
            $table->enum('ambito_gasto', ['propiedad', 'contrato']);
            $table->enum('pagador_gasto', ['arrendador', 'inquilino'])->default('inquilino');
            $table->string('periodicidad_gasto', 30)->default('mensual');
            $table->unsignedTinyInteger('dia_vencimiento')->default(5);
            $table->date('fecha_inicio_gasto');
            $table->date('fecha_fin_gasto')->nullable();
            $table->string('estado_gasto', 30)->default('activo');
            $table->timestamp('creado_gasto')->nullable();
            $table->timestamp('actualizado_gasto')->nullable();

            $table->index('id_propiedad_fk');
            $table->index('id_alquiler_fk');
            $table->index('id_gestor_fk');
            $table->index('estado_gasto');

            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')->on('tbl_propiedad')
                ->onDelete('restrict');

            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')->on('tbl_alquiler')
                ->onDelete('cascade');

            $table->foreign('id_gestor_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_gasto');
    }
};
