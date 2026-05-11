<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_propiedad_permisos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_propiedad_fk');
            $table->unsignedBigInteger('id_usuario_fk');

            //Incidencias
            $table->boolean('incidencias')->default(false);
            //Gastos
            $table->boolean('gastos')->default(false);
            //Chat
            $table->boolean('chat')->default(false);
            //Editar propiedad
            $table->boolean('editar_propiedad')->default(false);
            $table->timestamps();

            $table->foreign('id_propiedad_fk')
                ->references('id_propiedad')
                ->on('tbl_propiedad')
                ->onDelete('cascade');

            $table->foreign('id_usuario_fk')
                ->references('id_usuario')
                ->on('tbl_usuario')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_propiedad_permisos');
    }
};
