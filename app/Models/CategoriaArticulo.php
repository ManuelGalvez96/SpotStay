<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaArticulo extends Model
{
    protected $table = 'tbl_asesoria_categoria';

    protected $fillable = [
        'nombre',
        'slug',
        'icono',
        'orden',
        'estado',
    ];

    public function articulos(): HasMany
    {
        return $this->hasMany(ArticuloAsesoria::class, 'id_categoria_fk');
    }
}
