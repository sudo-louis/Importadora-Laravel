<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'contenedor_id',
        'fecha_pago',
        'impuestos',
        'honorarios',
        'maniobras',
        'almacenaje',
        // 'total' es generado en DB (según tu comentario)
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class, 'contenedor_id');
    }
}
