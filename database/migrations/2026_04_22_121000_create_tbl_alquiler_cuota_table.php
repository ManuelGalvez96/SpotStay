<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_alquiler_cuota', function (Blueprint $table) {
            $table->id('id_alquiler_cuota');
            $table->unsignedBigInteger('id_alquiler_fk');

            $table->date('mes_cuota');
            $table->decimal('importe_base', 10, 2);

            $table->enum('estado', ['pendiente', 'pagado', 'atrasado'])->default('pendiente');

            $table->date('fecha_vencimiento');
            $table->timestamp('pagado_en')->nullable();

            $table->timestamps();

            $table->foreign('id_alquiler_fk')
                ->references('id_alquiler')->on('tbl_alquiler')
                ->onDelete('cascade');

            $table->unique(['id_alquiler_fk', 'mes_cuota']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_alquiler_cuota');
    }
};
