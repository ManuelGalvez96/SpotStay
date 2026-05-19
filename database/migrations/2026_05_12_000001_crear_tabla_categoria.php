<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_categoria', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->string('nombre_categoria', 100)->unique();
            $table->text('descripcion_categoria')->nullable();
            $table->enum('estado_categoria', ['activa', 'inactiva'])->default('activa');
            $table->timestamp('creado_categoria')->useCurrent();
            $table->timestamp('actualizado_categoria')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_categoria');
    }
};
