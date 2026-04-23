<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlquilerCuota extends Model
{
    protected $table = 'tbl_alquiler_cuota';
    protected $primaryKey = 'id_alquiler_cuota';

    protected $fillable = [
        'id_alquiler_fk',
        'mes_cuota',
        'importe_base',
        'estado',
        'fecha_vencimiento',
        'pagado_en',
    ];

    protected $casts = [
        'mes_cuota' => 'date',
        'importe_base' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'pagado_en' => 'datetime',
    ];

    public function setMesCuotaAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['mes_cuota'] = null;
            return;
        }

        $this->attributes['mes_cuota'] = Carbon::parse((string) $value)
            ->startOfMonth()
            ->toDateString();
    }

    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class, 'id_alquiler_fk', 'id_alquiler');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'id_alquiler_cuota_fk', 'id_alquiler_cuota');
    }
}
