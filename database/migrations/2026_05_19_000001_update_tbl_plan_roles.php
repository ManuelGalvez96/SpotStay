<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tbl_plan') && Schema::hasColumn('tbl_plan', 'rol_destino')) {
            // Ampliar el enum para incluir inquilino y gestor
            DB::statement("ALTER TABLE tbl_plan MODIFY COLUMN rol_destino ENUM('miembro','arrendador','inquilino','gestor') DEFAULT 'arrendador'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_plan') && Schema::hasColumn('tbl_plan', 'rol_destino')) {
            // Volver al enum original (nota: esto fallará si existen valores no permitidos en la columna)
            try {
                DB::statement("ALTER TABLE tbl_plan MODIFY COLUMN rol_destino ENUM('miembro','arrendador') DEFAULT 'arrendador'");
            } catch (\Throwable $e) {
                // No forzar la reversión si hay valores incompatibles
            }
        }
    }
};
