<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_incidencia', 'presupuesto_importe_incidencia')) {
                $table->decimal('presupuesto_importe_incidencia', 10, 2)->nullable()->after('esperando_de_incidencia');
            }

            if (!Schema::hasColumn('tbl_incidencia', 'detalle_presupuesto_incidencia')) {
                $table->text('detalle_presupuesto_incidencia')->nullable()->after('presupuesto_importe_incidencia');
            }

            if (!Schema::hasColumn('tbl_incidencia', 'responsable_pago_incidencia')) {
                $table->string('responsable_pago_incidencia', 30)->nullable()->after('detalle_presupuesto_incidencia');
                $table->index('responsable_pago_incidencia');
            }

            if (!Schema::hasColumn('tbl_incidencia', 'pagado_presupuesto_incidencia')) {
                $table->boolean('pagado_presupuesto_incidencia')->default(false)->after('responsable_pago_incidencia');
            }

            if (!Schema::hasColumn('tbl_incidencia', 'pagado_incidencia')) {
                $table->timestamp('pagado_incidencia')->nullable()->after('pagado_presupuesto_incidencia');
            }

            if (!Schema::hasColumn('tbl_incidencia', 'cerrado_incidencia')) {
                $table->timestamp('cerrado_incidencia')->nullable()->after('resuelto_incidencia');
                $table->index('cerrado_incidencia');
            }
        });

        DB::statement(
            'UPDATE tbl_incidencia i
             INNER JOIN tbl_propiedad p ON p.id_propiedad = i.id_propiedad_fk
             SET i.id_asignado_fk = COALESCE(p.id_gestor_fk, p.id_arrendador_fk)
             WHERE i.id_asignado_fk IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('tbl_incidencia', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_incidencia', 'responsable_pago_incidencia')) {
                $table->dropIndex(['responsable_pago_incidencia']);
            }

            if (Schema::hasColumn('tbl_incidencia', 'cerrado_incidencia')) {
                $table->dropIndex(['cerrado_incidencia']);
            }

            $columnsToDrop = [];
            foreach ([
                'presupuesto_importe_incidencia',
                'detalle_presupuesto_incidencia',
                'responsable_pago_incidencia',
                'pagado_presupuesto_incidencia',
                'pagado_incidencia',
                'cerrado_incidencia',
            ] as $column) {
                if (Schema::hasColumn('tbl_incidencia', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
