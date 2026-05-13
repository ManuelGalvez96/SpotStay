<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            // Eliminar el campo string redundante
            $table->dropColumn('categoria_incidencia');
            // Hacer id_categoria_fk NOT NULL ya que siempre debe tener categoría
            $table->unsignedBigInteger('id_categoria_fk')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            // Restaurar para rollback si es necesario
            $table->string('categoria_incidencia')->nullable();
            $table->unsignedBigInteger('id_categoria_fk')->nullable()->change();
        });
    }
};
