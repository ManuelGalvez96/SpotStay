<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_gasto_cuota', function (Blueprint $table) {
            $table->unsignedBigInteger('id_gasto_cuota')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_gasto_fk');
            $table->date('mes_cuota');
            $table->date('vencimiento_cuota');
            $table->decimal('importe_total_cuota', 10, 2);
            $table->string('estado_cuota', 30)->default('pendiente');
            $table->timestamp('pagado_cuota')->nullable();
            $table->timestamp('creado_cuota')->nullable();
            $table->timestamp('actualizado_cuota')->nullable();

            $table->index('id_gasto_fk');
            $table->index('mes_cuota');
            $table->index('estado_cuota');
            $table->unique(['id_gasto_fk', 'mes_cuota'], 'uq_gasto_mes');

            $table->foreign('id_gasto_fk')
                ->references('id_gasto')->on('tbl_gasto')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_gasto_cuota');
    }
};
