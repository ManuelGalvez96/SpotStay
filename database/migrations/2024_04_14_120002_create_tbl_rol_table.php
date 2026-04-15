<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de roles del sistema (admin, arrendador, inquilino, etc.)
     */
    public function up(): void
    {
        Schema::create('tbl_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rol')->autoIncrement()->primary();
            $table->string('nombre_rol', 50);
            $table->string('slug_rol', 50)->unique();
            $table->timestamp('creado_rol')->nullable();
            $table->timestamp('actualizado_rol')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_rol');
    }
};
