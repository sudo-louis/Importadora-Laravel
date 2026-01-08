<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Liberacion extends Model
{
    protected $table = 'liberaciones';

    protected $fillable = [
        'contenedor_id',
        'naviera',
        'dias_libres',
        'revalidacion',
        'fecha_revalidacion',
        'costo_liberacion',
        'fecha_liberacion',
        'garantia',
        'fecha_garantia',
        'devolucion_garantia',
        'costos_demora',
        'fecha_demora',
        'flete_maritimo',
        'fecha_flete',
    ];

    protected $casts = [
        'revalidacion' => 'boolean',
        'fecha_revalidacion' => 'date',
        'fecha_liberacion' => 'date',
        'fecha_garantia' => 'date',
        'fecha_demora' => 'date',
        'fecha_flete' => 'date',
        'costo_liberacion' => 'decimal:2',
        'garantia' => 'decimal:2',
        'costos_demora' => 'decimal:2',
        'flete_maritimo' => 'decimal:2',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class);
    }
}
