<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'tbl_solicitud_alquiler';
    protected $primaryKey = 'id_solicitud_alquiler';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'propiedad_id',
        'usuario_id',
        'mensaje',
        'fecha_entrada',
        'estado',
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
    ];
}
