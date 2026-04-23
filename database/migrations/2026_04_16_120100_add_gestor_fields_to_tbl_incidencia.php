<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            $table->string('esperando_de_incidencia', 30)->nullable()->after('estado_incidencia');
            $table->timestamp('resuelto_incidencia')->nullable()->after('actualizado_incidencia');

            $table->index('esperando_de_incidencia');
            $table->index('resuelto_incidencia');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            $table->dropIndex(['esperando_de_incidencia']);
            $table->dropIndex(['resuelto_incidencia']);

            $table->dropColumn('esperando_de_incidencia');
            $table->dropColumn('resuelto_incidencia');
        });
    }
};
