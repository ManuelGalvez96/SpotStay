<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Añadimos el campo google_id para el inicio de sesión con redes sociales
     * y permitimos que la contraseña sea nula para estos usuarios.
     */
    public function up(): void
    {
        Schema::table('tbl_usuario', function (Blueprint $table) {
            $table->string('google_id')->nullable()->after('email_usuario');
            $table->string('contrasena_usuario')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_usuario', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->string('contrasena_usuario')->nullable(false)->change();
        });
    }
};
