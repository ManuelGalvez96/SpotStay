<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agregar columna es_principal_foto a tbl_fotos
     */
    public function up(): void
    {
        Schema::table('tbl_fotos', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_fotos', 'es_principal_foto')) {
                $table->boolean('es_principal_foto')->default(false)->after('ruta_foto');
                $table->index('es_principal_foto');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_fotos', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_fotos', 'es_principal_foto')) {
                $table->dropIndex(['es_principal_foto']);
                $table->dropColumn('es_principal_foto');
            }
        });
    }
};
