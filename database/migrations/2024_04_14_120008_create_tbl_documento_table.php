<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tabla de documentos: almacena referencias a contratos, PDF, facturas, etc.
     */
    public function up(): void
    {
        Schema::create('tbl_documento', function (Blueprint $table) {
            $table->unsignedBigInteger('id_documento')->autoIncrement()->primary();
            $table->unsignedBigInteger('id_usuario_fk');
            $table->string('tipo_documento', 50);
            $table->string('tipo_entidad_documento', 50);
            $table->unsignedBigInteger('id_entidad_documento');
            $table->string('nombre_documento', 200);
            $table->string('url_documento', 500);
            $table->string('hash_documento', 64);
            $table->string('pdfmonkey_id_documento', 100)->nullable();
            $table->timestamp('creado_documento')->nullable();
            $table->timestamp('actualizado_documento')->nullable();

            // Índices
            $table->index('id_usuario_fk');
            $table->index(['tipo_entidad_documento', 'id_entidad_documento']);
            $table->index('id_entidad_documento');

            // Foreign keys
            $table->foreign('id_usuario_fk')
                ->references('id_usuario')->on('tbl_usuario')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_documento');
    }
};
