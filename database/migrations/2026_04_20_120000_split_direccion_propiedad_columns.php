<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_propiedad', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_propiedad', 'calle_propiedad')) {
                $table->string('calle_propiedad', 150)->nullable()->after('titulo_propiedad');
            }
            if (!Schema::hasColumn('tbl_propiedad', 'numero_propiedad')) {
                $table->string('numero_propiedad', 20)->nullable()->after('calle_propiedad');
            }
            if (!Schema::hasColumn('tbl_propiedad', 'piso_propiedad')) {
                $table->string('piso_propiedad', 20)->nullable()->after('numero_propiedad');
            }
            if (!Schema::hasColumn('tbl_propiedad', 'puerta_propiedad')) {
                $table->string('puerta_propiedad', 20)->nullable()->after('piso_propiedad');
            }
        });

        if (Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
            DB::statement("UPDATE tbl_propiedad
                SET
                    calle_propiedad = TRIM(SUBSTRING_INDEX(direccion_propiedad, ' ', CHAR_LENGTH(direccion_propiedad) - CHAR_LENGTH(REPLACE(direccion_propiedad, ' ', '')))),
                    numero_propiedad = TRIM(SUBSTRING_INDEX(direccion_propiedad, ' ', -1))
                WHERE direccion_propiedad IS NOT NULL AND direccion_propiedad <> ''");

            Schema::table('tbl_propiedad', function (Blueprint $table) {
                $table->dropColumn('direccion_propiedad');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tbl_propiedad', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_propiedad', 'direccion_propiedad')) {
                $table->string('direccion_propiedad')->nullable()->after('titulo_propiedad');
            }
        });

        DB::statement("UPDATE tbl_propiedad
            SET direccion_propiedad = TRIM(CONCAT_WS(' ', calle_propiedad, numero_propiedad))");

        Schema::table('tbl_propiedad', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_propiedad', 'puerta_propiedad')) {
                $table->dropColumn('puerta_propiedad');
            }
            if (Schema::hasColumn('tbl_propiedad', 'piso_propiedad')) {
                $table->dropColumn('piso_propiedad');
            }
            if (Schema::hasColumn('tbl_propiedad', 'numero_propiedad')) {
                $table->dropColumn('numero_propiedad');
            }
            if (Schema::hasColumn('tbl_propiedad', 'calle_propiedad')) {
                $table->dropColumn('calle_propiedad');
            }
        });
    }
};
