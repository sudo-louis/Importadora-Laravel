<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'contenedor_id',
        'tipo',
        'descripcion',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class);
    }
}
