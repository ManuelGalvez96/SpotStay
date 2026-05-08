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
        Schema::table('tbl_plan', function (Blueprint $table) {
            $table->enum('rol_destino', ['miembro', 'arrendador'])->default('arrendador')->after('slug_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_plan', function (Blueprint $table) {
            $table->dropColumn('rol_destino');
        });
    }
};
