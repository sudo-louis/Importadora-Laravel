<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Despacho extends Model
{
    protected $table = 'despachos';

    protected $fillable = [
        'contenedor_id',
        'numero_pedimento',
        'clave_pedimento',
        'importador',
        'tipo_carga',
        'fecha_carga',
        'reconocimiento_aduanero',
        'fecha_pago',
        'fecha_modulacion',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_carga' => 'date',
        'reconocimiento_aduanero' => 'date',
        'fecha_pago' => 'date',
        'fecha_modulacion' => 'date',
        'fecha_entrega' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class, 'contenedor_id');
    }
}
