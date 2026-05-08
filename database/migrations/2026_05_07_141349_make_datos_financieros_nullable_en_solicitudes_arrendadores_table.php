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
        Schema::table('tbl_solicitud_arrendador', function (Blueprint $table) {
            $table->string('iban_solicitud', 34)->nullable()->change();
            $table->string('titular_cuenta_solicitud', 100)->nullable()->change();
            $table->string('nif_solicitud', 20)->nullable()->change();
            $table->string('direccion_fiscal_solicitud', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_solicitud_arrendador', function (Blueprint $table) {
            $table->string('iban_solicitud', 34)->nullable(false)->change();
            $table->string('titular_cuenta_solicitud', 100)->nullable(false)->change();
            $table->string('nif_solicitud', 20)->nullable(false)->change();
            $table->string('direccion_fiscal_solicitud', 255)->nullable(false)->change();
        });
    }
};
