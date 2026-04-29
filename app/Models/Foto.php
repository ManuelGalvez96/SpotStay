<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Foto extends Model
{
    protected $table = 'tbl_fotos';
    protected $primaryKey = 'id_foto';
    public $timestamps = false;

    protected $fillable = [
        'id_propiedad_fk',
        'ruta_foto',
        'orden',
        'creado_foto',
    ];

    protected $casts = [
        'creado_foto' => 'datetime',
        'orden' => 'integer',
    ];

    // Relación con la propiedad a la que pertenece la foto
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class, 'id_propiedad_fk', 'id_propiedad');
    }
}
