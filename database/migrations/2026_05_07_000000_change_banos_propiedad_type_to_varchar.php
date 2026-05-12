<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Cambiar tipo de banos_propiedad de TINYINT a VARCHAR(10)
     * Permite almacenar valores como "3+" junto con números
     */
    public function up(): void
    {
        Schema::table('tbl_propiedad', function (Blueprint $table) {
            $table->string('banos_propiedad', 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_propiedad', function (Blueprint $table) {
            $table->tinyInteger('banos_propiedad')->unsigned()->nullable()->change();
        });
    }
};
