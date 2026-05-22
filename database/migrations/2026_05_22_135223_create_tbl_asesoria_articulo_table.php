<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_asesoria_articulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_categoria_fk')->constrained('tbl_asesoria_categoria')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('slug')->unique();
            $table->boolean('estado')->default(true);
            $table->boolean('destacado')->default(false);
            $table->unsignedSmallInteger('orden_faq')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_asesoria_articulo');
    }
};
