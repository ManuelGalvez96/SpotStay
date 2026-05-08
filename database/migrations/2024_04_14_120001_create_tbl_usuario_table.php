<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla base de usuarios del sistema
     */
    public function up(): void
    {
        Schema::create('tbl_usuario', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario')->autoIncrement()->primary();
            $table->string('nombre_usuario', 100);
            $table->string('email_usuario', 150)->unique();
            $table->string('stripe_id')->nullable()->index();
            $table->string('stripe_account_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_status')->nullable();
            $table->string('contrasena_usuario');
            $table->string('telefono_usuario', 20)->nullable();
            $table->string('avatar_usuario')->nullable();
            $table->string('dni_usuario', 20)->nullable();
            $table->date('fecha_nacimiento_usuario')->nullable();
            $table->string('iban_usuario', 34)->nullable();
            $table->string('direccion_fiscal_usuario', 255)->nullable();
            $table->string('tipo_arrendador_usuario', 20)->nullable();
            $table->boolean('verificado_identidad_usuario')->default(false);
            $table->boolean('activo_usuario')->default(true);
            $table->timestamp('verificado_usuario')->nullable();
            $table->string('token_usuario', 100)->nullable();
            $table->timestamp('creado_usuario')->nullable();
            $table->timestamp('actualizado_usuario')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usuario');
    }
};
