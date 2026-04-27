<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tbl_pago')) {
            return;
        }

        Schema::table('tbl_pago', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_pago', 'id_alquiler_cuota_fk')) {
                $table->unsignedBigInteger('id_alquiler_cuota_fk')->nullable()->after('id_alquiler_fk');
                $table->index('id_alquiler_cuota_fk');
                $table->foreign('id_alquiler_cuota_fk')
                    ->references('id_alquiler_cuota')->on('tbl_alquiler_cuota')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('tbl_pago')) {
            return;
        }

        Schema::table('tbl_pago', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_pago', 'id_alquiler_cuota_fk')) {
                $table->dropForeign(['id_alquiler_cuota_fk']);
                $table->dropIndex(['id_alquiler_cuota_fk']);
                $table->dropColumn('id_alquiler_cuota_fk');
            }
        });
    }
};
