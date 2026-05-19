<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tbl_pago MODIFY COLUMN tipo_pago ENUM('alquiler', 'gasto', 'fianza', 'incidencia', 'suscripcion', 'liquidacion') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tbl_pago MODIFY COLUMN tipo_pago ENUM('alquiler', 'gasto', 'fianza', 'incidencia', 'suscripcion') NOT NULL");
    }
};
