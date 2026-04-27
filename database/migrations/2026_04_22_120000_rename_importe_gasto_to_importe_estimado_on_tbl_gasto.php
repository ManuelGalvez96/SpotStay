<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tbl_gasto')) {
            return;
        }

        if (Schema::hasColumn('tbl_gasto', 'importe_gasto') && !Schema::hasColumn('tbl_gasto', 'importe_estimado')) {
            DB::statement('ALTER TABLE tbl_gasto CHANGE importe_gasto importe_estimado DECIMAL(10,2) NULL');
            return;
        }

        if (Schema::hasColumn('tbl_gasto', 'importe_estimado')) {
            DB::statement('ALTER TABLE tbl_gasto MODIFY importe_estimado DECIMAL(10,2) NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tbl_gasto')) {
            return;
        }

        if (Schema::hasColumn('tbl_gasto', 'importe_estimado') && !Schema::hasColumn('tbl_gasto', 'importe_gasto')) {
            DB::statement('UPDATE tbl_gasto SET importe_estimado = 0.00 WHERE importe_estimado IS NULL');
            DB::statement('ALTER TABLE tbl_gasto CHANGE importe_estimado importe_gasto DECIMAL(10,2) NOT NULL');
            return;
        }

        if (Schema::hasColumn('tbl_gasto', 'importe_gasto')) {
            DB::statement('UPDATE tbl_gasto SET importe_gasto = 0.00 WHERE importe_gasto IS NULL');
            DB::statement('ALTER TABLE tbl_gasto MODIFY importe_gasto DECIMAL(10,2) NOT NULL');
        }
    }
};
