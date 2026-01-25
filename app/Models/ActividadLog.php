<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActividadLog extends Model
{
    protected $table = 'actividad_logs';

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_hora' => 'datetime',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contenedor()
    {
        return $this->belongsTo(Contenedor::class);
    }
}
