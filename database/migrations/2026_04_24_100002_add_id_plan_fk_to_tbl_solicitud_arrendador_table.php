<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_solicitud_arrendador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_plan_fk')->nullable()->after('id_usuario_fk');

            $table->foreign('id_plan_fk')
                ->references('id_plan')->on('tbl_plan')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_solicitud_arrendador', function (Blueprint $table) {
            $table->dropForeign(['id_plan_fk']);
            $table->dropColumn('id_plan_fk');
        });
    }
};
