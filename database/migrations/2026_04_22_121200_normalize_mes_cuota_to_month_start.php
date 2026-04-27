<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tbl_alquiler_cuota')) {
            return;
        }

        // Fuerza todas las cuotas existentes al primer dia del mes (YYYY-MM-01).
        DB::statement('UPDATE tbl_alquiler_cuota SET mes_cuota = DATE_FORMAT(mes_cuota, "%Y-%m-01")');
    }

    public function down(): void
    {
        // No reversible without original day values.
    }
};
