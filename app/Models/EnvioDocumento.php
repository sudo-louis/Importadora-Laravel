<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioDocumento extends Model
{
    protected $table = 'envio_documentos';

    protected $fillable = [
        'contenedor_id',
        'enviado',
        'fecha_envio',
    ];

    protected $casts = [
        'enviado' => 'boolean',
        'fecha_envio' => 'date',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class);
    }
}
