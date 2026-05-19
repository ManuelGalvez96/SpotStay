<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Crear tabla tbl_plan
     * Catálogo global de planes disponibles (diferente de tbl_suscripcion)
     * Incluye inserción de 3 planes base
     */
    public function up(): void
    {
        Schema::create('tbl_plan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_plan')->autoIncrement()->primary();
            $table->string('nombre_plan', 50);
            $table->string('slug_plan', 30)->unique();
            $table->enum('rol_destino', ['miembro', 'arrendador'])->default('arrendador');
            $table->decimal('precio_plan', 8, 2)->default(0.00);
            $table->unsignedTinyInteger('max_propiedades_plan')->default(1);
            $table->text('descripcion_plan')->nullable();
            $table->boolean('activo_plan')->default(true);
            $table->timestamp('creado_plan')->nullable();
            $table->timestamp('actualizado_plan')->nullable();

            $table->index('slug_plan');
            $table->index('activo_plan');
        });

        // Insertar los 3 planes base
        DB::table('tbl_plan')->insert([
            [
                'nombre_plan' => 'Gratuito',
                'slug_plan' => 'gratuito',
                'rol_destino' => 'miembro',
                'precio_plan' => 0.00,
                'max_propiedades_plan' => 1,
                'descripcion_plan' => 'Plan básico sin coste para empezar',
                'activo_plan' => true,
                'creado_plan' => now(),
                'actualizado_plan' => now(),
            ],
            [
                'nombre_plan' => 'Básico',
                'slug_plan' => 'basico',
                'rol_destino' => 'arrendador',
                'precio_plan' => 9.99,
                'max_propiedades_plan' => 3,
                'descripcion_plan' => 'Plan para arrendadores con pocas propiedades',
                'activo_plan' => true,
                'creado_plan' => now(),
                'actualizado_plan' => now(),
            ],
            [
                'nombre_plan' => 'Pro',
                'slug_plan' => 'pro',
                'rol_destino' => 'arrendador',
                'precio_plan' => 29.99,
                'max_propiedades_plan' => 10,
                'descripcion_plan' => 'Plan para arrendadores con muchas propiedades',
                'activo_plan' => true,
                'creado_plan' => now(),
                'actualizado_plan' => now(),
            ],
        ]);

        if (Schema::hasTable('tbl_suscripcion') && Schema::hasColumn('tbl_suscripcion', 'id_plan_fk')) {
            try {
                DB::statement('ALTER TABLE tbl_suscripcion ADD CONSTRAINT tbl_suscripcion_id_plan_fk_foreign FOREIGN KEY (id_plan_fk) REFERENCES tbl_plan(id_plan) ON DELETE RESTRICT');
            } catch (\Throwable $e) {
                // La FK puede existir ya en algunos entornos.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tbl_suscripcion')) {
            try {
                DB::statement('ALTER TABLE tbl_suscripcion DROP FOREIGN KEY tbl_suscripcion_id_plan_fk_foreign');
            } catch (\Throwable $e) {
                // Puede no existir si no se llegó a crear.
            }
        }

        Schema::dropIfExists('tbl_plan');
    }
};
