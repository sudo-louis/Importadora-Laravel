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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function cotizacion(): HasOne
    {
        return $this->hasOne(Cotizacion::class, 'contenedor_id');
    }

    public function despacho(): HasOne
    {
        return $this->hasOne(Despacho::class, 'contenedor_id');
    }

    public function gastosLiberacion(): HasMany
    {
        return $this->hasMany(Gasto::class, 'contenedor_id')
            ->where('tipo', 'liberacion');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class, 'contenedor_id')
            ->where('tipo', 'general');
    }

    public function actividadLogs(): HasMany
    {
        return $this->hasMany(ActividadLog::class, 'contenedor_id');
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
}
