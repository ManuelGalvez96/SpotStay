<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'tbl_categoria';
    protected $primaryKey = 'id_categoria';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;
    const CREATED_AT = 'creado_categoria';
    const UPDATED_AT = 'actualizado_categoria';

    protected $fillable = [
        'nombre_categoria',
        'descripcion_categoria',
        'estado_categoria',
    ];

    protected $casts = [
        'creado_categoria' => 'datetime',
        'actualizado_categoria' => 'datetime',
    ];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_categoria_fk', 'id_categoria');
    }
}
