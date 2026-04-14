<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Documento
 * 
 * Tabla de documentos - almacena documentos organizacionales generados por el sistema
 */
class Documento extends Model
{
    protected $table = 'tbl_documento';
    protected $primaryKey = 'id_documento';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_fk',
        'tipo_documento',
        'tipo_entidad_documento',
        'id_entidad_documento',
        'nombre_documento',
        'url_documento',
        'hash_documento',
        'pdfmonkey_id_documento',
        'creado_documento',
        'actualizado_documento',
    ];

    protected $casts = [
        'creado_documento' => 'datetime',
        'actualizado_documento' => 'datetime',
    ];

    // Usuario propietario del documento
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_fk', 'id_usuario');
    }
}
