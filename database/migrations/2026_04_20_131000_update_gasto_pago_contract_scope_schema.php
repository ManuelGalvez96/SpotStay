<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tbl_gasto')) {
            $afterColumn = Schema::hasColumn('tbl_gasto', 'importe_estimado') ? 'importe_estimado' : 'importe_gasto';

            Schema::table('tbl_gasto', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_gasto', 'id_alquiler_fk')) {
                    $table->unsignedBigInteger('id_alquiler_fk')->nullable()->after('id_propiedad_fk');
                }
            });

            if (!Schema::hasColumn('tbl_gasto', 'ambito_gasto')) {
                Schema::table('tbl_gasto', function (Blueprint $table) use ($afterColumn) {
                    $table->enum('ambito_gasto', ['propiedad', 'contrato'])->default('propiedad')->after($afterColumn);
                });
            }

            DB::statement("UPDATE tbl_gasto SET pagador_gasto = 'inquilino' WHERE pagador_gasto IN ('gestor', 'inquilinos')");
            DB::statement("ALTER TABLE tbl_gasto MODIFY pagador_gasto ENUM('arrendador','inquilino') NOT NULL DEFAULT 'inquilino'");
            DB::statement("UPDATE tbl_gasto SET ambito_gasto = 'propiedad' WHERE ambito_gasto IS NULL OR ambito_gasto = ''");

            try {
                DB::statement('ALTER TABLE tbl_gasto ADD CONSTRAINT tbl_gasto_id_alquiler_fk_foreign FOREIGN KEY (id_alquiler_fk) REFERENCES tbl_alquiler(id_alquiler) ON DELETE CASCADE');
            } catch (\Throwable $e) {
                // Already exists.
            }
        }

        if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
            Schema::table('tbl_gasto_cuota_detalle', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_gasto_cuota_detalle', 'id_alquiler_fk')) {
                    $table->unsignedBigInteger('id_alquiler_fk')->nullable()->after('id_gasto_cuota_fk');
                }
            });

            DB::statement("UPDATE tbl_gasto_cuota_detalle d
                JOIN tbl_gasto_cuota c ON c.id_gasto_cuota = d.id_gasto_cuota_fk
                JOIN tbl_gasto g ON g.id_gasto = c.id_gasto_fk
                JOIN tbl_alquiler a ON a.id_propiedad_fk = g.id_propiedad_fk
                SET d.id_alquiler_fk = a.id_alquiler
                WHERE d.id_alquiler_fk IS NULL");

            DB::statement("ALTER TABLE tbl_gasto_cuota_detalle MODIFY id_alquiler_fk BIGINT UNSIGNED NOT NULL");

            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle DROP INDEX uq_cuota_pagador');
            } catch (\Throwable $e) {
                // It may not exist.
            }

            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle ADD UNIQUE uq_cuota_alquiler (id_gasto_cuota_fk, id_alquiler_fk)');
            } catch (\Throwable $e) {
                // Already exists.
            }

            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle ADD CONSTRAINT tbl_gasto_cuota_detalle_id_alquiler_fk_foreign FOREIGN KEY (id_alquiler_fk) REFERENCES tbl_alquiler(id_alquiler) ON DELETE CASCADE');
            } catch (\Throwable $e) {
                // Already exists.
            }
        }

        if (Schema::hasTable('tbl_pago')) {
            Schema::table('tbl_pago', function (Blueprint $table) {
                if (!Schema::hasColumn('tbl_pago', 'id_gasto_cuota_detalle_fk')) {
                    $table->unsignedBigInteger('id_gasto_cuota_detalle_fk')->nullable()->after('id_pagador_fk');
                }
                if (!Schema::hasColumn('tbl_pago', 'id_gasto_cuota_fk')) {
                    $table->unsignedBigInteger('id_gasto_cuota_fk')->nullable()->after('id_gasto_cuota_detalle_fk');
                }
            });

            DB::statement("UPDATE tbl_pago SET tipo_pago = 'alquiler' WHERE tipo_pago IN ('renta', 'mensualidad')");
            DB::statement("UPDATE tbl_pago SET tipo_pago = 'gasto' WHERE tipo_pago = 'servicios'");
            DB::statement("ALTER TABLE tbl_pago MODIFY tipo_pago ENUM('alquiler','gasto','fianza') NOT NULL");

            try {
                DB::statement('ALTER TABLE tbl_pago ADD CONSTRAINT tbl_pago_id_gasto_cuota_detalle_fk_foreign FOREIGN KEY (id_gasto_cuota_detalle_fk) REFERENCES tbl_gasto_cuota_detalle(id_gasto_cuota_detalle) ON DELETE SET NULL');
            } catch (\Throwable $e) {
                // Already exists.
            }

            try {
                DB::statement('ALTER TABLE tbl_pago ADD CONSTRAINT tbl_pago_id_gasto_cuota_fk_foreign FOREIGN KEY (id_gasto_cuota_fk) REFERENCES tbl_gasto_cuota(id_gasto_cuota) ON DELETE SET NULL');
            } catch (\Throwable $e) {
                // Already exists.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_pago')) {
            try {
                DB::statement('ALTER TABLE tbl_pago DROP FOREIGN KEY tbl_pago_id_gasto_cuota_detalle_fk_foreign');
            } catch (\Throwable $e) {
            }

            try {
                DB::statement('ALTER TABLE tbl_pago DROP FOREIGN KEY tbl_pago_id_gasto_cuota_fk_foreign');
            } catch (\Throwable $e) {
            }

            Schema::table('tbl_pago', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_pago', 'id_gasto_cuota_detalle_fk')) {
                    $table->dropColumn('id_gasto_cuota_detalle_fk');
                }
                if (Schema::hasColumn('tbl_pago', 'id_gasto_cuota_fk')) {
                    $table->dropColumn('id_gasto_cuota_fk');
                }
            });

            DB::statement("ALTER TABLE tbl_pago MODIFY tipo_pago VARCHAR(30) NOT NULL");
        }

        if (Schema::hasTable('tbl_gasto_cuota_detalle')) {
            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle DROP FOREIGN KEY tbl_gasto_cuota_detalle_id_alquiler_fk_foreign');
            } catch (\Throwable $e) {
            }

            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle DROP INDEX uq_cuota_alquiler');
            } catch (\Throwable $e) {
            }

            try {
                DB::statement('ALTER TABLE tbl_gasto_cuota_detalle ADD UNIQUE uq_cuota_pagador (id_gasto_cuota_fk, id_pagador_fk)');
            } catch (\Throwable $e) {
            }

            Schema::table('tbl_gasto_cuota_detalle', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_gasto_cuota_detalle', 'id_alquiler_fk')) {
                    $table->dropColumn('id_alquiler_fk');
                }
            });
        }

        if (Schema::hasTable('tbl_gasto')) {
            try {
                DB::statement('ALTER TABLE tbl_gasto DROP FOREIGN KEY tbl_gasto_id_alquiler_fk_foreign');
            } catch (\Throwable $e) {
            }

            Schema::table('tbl_gasto', function (Blueprint $table) {
                if (Schema::hasColumn('tbl_gasto', 'id_alquiler_fk')) {
                    $table->dropColumn('id_alquiler_fk');
                }
                if (Schema::hasColumn('tbl_gasto', 'ambito_gasto')) {
                    $table->dropColumn('ambito_gasto');
                }
            });

            DB::statement("ALTER TABLE tbl_gasto MODIFY pagador_gasto VARCHAR(30) NOT NULL DEFAULT 'gestor'");
        }
    }
};
