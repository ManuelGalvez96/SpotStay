<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_categoria_fk')->nullable()->after('id_propiedad_fk');
            $table->foreign('id_categoria_fk')
                ->references('id_categoria')
                ->on('tbl_categoria')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            $table->dropForeign(['id_categoria_fk']);
            $table->dropColumn('id_categoria_fk');
        });
    }
};
