<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla específica para usuarios con rol ARRENDADOR
     * Contiene campos únicos de arrendadores (financieros, documentos, propiedades)
     */
    public function up(): void
    {
        Schema::create('tbl_usuario_arrendador', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario_arrendador')->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            
            // Campos financieros y documentación
            $table->string('nif_arrendador', 20)->nullable();
            $table->string('iban_arrendador', 34)->nullable();
            $table->string('entidad_bancaria_arrendador', 100)->nullable();
            $table->string('titular_cuenta_arrendador', 150)->nullable();
            
            // Información legal y verificación
            $table->boolean('verificado_identidad_arrendador')->default(false);
            $table->string('certificado_arrendador_ruta')->nullable();
            $table->timestamp('verificado_certificado_arrendador')->nullable();
            
            // Datos empresariales
            $table->string('tipo_arrendador', 30)->nullable(); // "particular", "empresa", "profesional"
            $table->string('nombre_empresa_arrendador', 150)->nullable();
            $table->string('cif_empresa_arrendador', 20)->nullable();
            
            // Dirección fiscal
            $table->string('direccion_fiscal_arrendador', 255)->nullable();
            $table->string('codigo_postal_arrendador', 10)->nullable();
            $table->string('ciudad_arrendador', 100)->nullable();
            $table->string('provincia_arrendador', 100)->nullable();
            
            // Estadísticas
            $table->integer('propiedades_activas')->default(0);
            $table->integer('propiedades_alquiladas')->default(0);
            $table->decimal('ingresos_totales', 12, 2)->default(0);
            $table->decimal('calificacion_arrendador', 3, 2)->nullable();
            
            // Timestamps
            $table->timestamp('creado_arrendador')->useCurrent();
            $table->timestamp('actualizado_arrendador')->useCurrent()->useCurrentOnUpdate();
            
            // Foreign key e índices
            $table->foreign('id_usuario_fk')
                  ->references('id_usuario')
                  ->on('tbl_usuario')
                  ->onDelete('cascade');
            
            $table->index('nif_arrendador');
            $table->index('iban_arrendador');
            $table->index('verificado_identidad_arrendador');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usuario_arrendador');
    }
};
