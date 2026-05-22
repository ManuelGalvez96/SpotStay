<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticuloAsesoria extends Model
{
    protected $table = 'tbl_asesoria_articulo';

    protected $fillable = [
        'id_categoria_fk',
        'titulo',
        'contenido',
        'slug',
        'estado',
        'destacado',
        'orden_faq',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaArticulo::class, 'id_categoria_fk');
    }
}
