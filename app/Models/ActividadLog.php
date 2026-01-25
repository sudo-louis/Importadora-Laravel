<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActividadLog extends Model
{
    protected $table = 'actividad_logs';

    protected $fillable = [
        'user_id',
        'contenedor_id',
        'accion',
        'modulo',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'fecha_hora',
    ];

    protected $casts = [
        // IMPORTANTE: esto asume que guardas JSON en longtext
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_hora' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedor::class, 'contenedor_id');
    }
}
