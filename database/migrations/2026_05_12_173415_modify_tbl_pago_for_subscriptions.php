<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Primero permitimos que id_alquiler_fk sea nulo (necesario para suscripciones)
        Schema::table('tbl_pago', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alquiler_fk')->nullable()->change();
        });

        // 2. Ampliamos el ENUM de tipo_pago para incluir 'suscripcion'
        // Usamos DB::statement porque el método ->change() de Laravel con ENUMs puede dar problemas según la versión
        DB::statement("ALTER TABLE tbl_pago MODIFY COLUMN tipo_pago ENUM('alquiler', 'gasto', 'fianza', 'incidencia', 'suscripcion') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_pago', function (Blueprint $table) {
            $table->unsignedBigInteger('id_alquiler_fk')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE tbl_pago MODIFY COLUMN tipo_pago ENUM('alquiler', 'gasto', 'fianza', 'incidencia') NOT NULL");
    }
};
