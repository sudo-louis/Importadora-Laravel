<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $table = 'despachos';

    protected $fillable = [
        'contenedor_id',

        // Pedimento
        'numero_pedimento',
        'clave_pedimento',
        'importador',

        // Proceso
        'tipo_carga',

        // Fechas
        'fecha_carga',
        'reconocimiento_aduanero',
        'fecha_pago',
        'fecha_modulacion',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_carga'             => 'date',
        'reconocimiento_aduanero' => 'date',
        'fecha_pago'              => 'date',
        'fecha_modulacion'        => 'date',
        'fecha_entrega'           => 'date',
    ];

    /**
     * Relación inversa
     */
    public function contenedor()
    {
        return $this->belongsTo(Contenedor::class);
    }
}
