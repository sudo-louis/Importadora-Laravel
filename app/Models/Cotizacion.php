<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        // 'total' NO porque es columna generada
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'impuestos' => 'decimal:2',
        'honorarios' => 'decimal:2',
        'maniobras' => 'decimal:2',
        'almacenaje' => 'decimal:2',
        'total' => 'decimal:2', // OK para leer
    ];
}
