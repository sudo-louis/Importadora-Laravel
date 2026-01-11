<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contenedor extends Model
{
    protected $table = 'contenedores';

    protected $fillable = [
        'numero_contenedor',
        'cliente',
        'fecha_llegada',
        'proveedor',
        'naviera',
        'mercancia_recibida',
        'estado',
        'created_by',
    ];

    protected $casts = [
        'fecha_llegada' => 'date',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function liberacion(): HasOne
    {
        return $this->hasOne(Liberacion::class, 'contenedor_id');
    }

    public function envioDocumento(): HasOne
    {
        return $this->hasOne(EnvioDocumento::class, 'contenedor_id');
    }

    public function cotizacion(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Cotizacion::class, 'contenedor_id');
    }


    public function gastosLiberacion(): HasMany
    {
        return $this->hasMany(Gasto::class, 'contenedor_id')->where('tipo', 'liberacion');
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'entregado' => 'Entregado',
            default => 'Desconocido',
        };
    }

    public function despacho()
    {
        return $this->hasOne(\App\Models\Despacho::class);
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'contenedor_id')->where('tipo', 'general');
    }
}
