<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de propiedades en alquiler
     */
    public function up(): void
    {
        Schema::create('tbl_propiedad', function (Blueprint $table) {
            $table->unsignedBigInteger('id_propiedad')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_arrendador_fk');
            $table->unsignedBigInteger('id_gestor_fk')->nullable();
            $table->string('titulo_propiedad', 150);
            $table->string('calle_propiedad', 150);
            $table->string('numero_propiedad', 20);
            $table->string('piso_propiedad', 20)->nullable();
            $table->string('puerta_propiedad', 20)->nullable();
            $table->string('ciudad_propiedad', 100);
            $table->string('codigo_postal_propiedad', 10);
            $table->decimal('latitud_propiedad', 10, 7)->nullable();
            $table->decimal('longitud_propiedad', 10, 7)->nullable();
            $table->text('descripcion_propiedad')->nullable();
            $table->decimal('precio_propiedad', 8, 2);
            $table->string('tipo_propiedad', 30)->nullable();
            $table->string('habitaciones_propiedad', 20)->nullable();
            $table->string('servicios_propiedad', 20)->nullable();
            $table->unsignedSmallInteger('metros_cuadrados_propiedad')->nullable();
            $table->boolean('amueblado_propiedad')->default(false);
            $table->boolean('piscina_propiedad')->default(false);
            $table->boolean('terraza_propiedad')->default(false);
            $table->boolean('garaje_propiedad')->default(false);
            $table->boolean('ascensor_propiedad')->default(false);
            $table->boolean('aire_acondicionado_propiedad')->default(false);
            $table->boolean('calefaccion_propiedad')->default(false);
            $table->boolean('trastero_propiedad')->default(false);
            $table->string('adicional_propiedad', 255)->nullable();
            $table->json('gastos_propiedad')->nullable();
            $table->string('estado_propiedad', 30)->default('borrador');
            $table->timestamp('creado_propiedad')->nullable();
            $table->timestamp('actualizado_propiedad')->nullable();

            // Índices
            $table->index('id_arrendador_fk');
            $table->index('estado_propiedad');
            $table->index('tipo_propiedad');
            $table->index('habitaciones_propiedad');
            $table->index('metros_cuadrados_propiedad');

            // Foreign keys
            $table->foreign('id_arrendador_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
            $table->foreign('id_gestor_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_propiedad');
    }
};
