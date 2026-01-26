<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plantilla extends Model
{
    protected $table = 'plantillas';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'predefinida',   // ✅ esta es la que usa tu sistema
        'created_by',
    ];

    protected $casts = [
        'predefinida' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ Compat: si en algún lado quedó "predeterminada", lo seguimos soportando
    public function getPredeterminadaAttribute()
    {
        return (bool) ($this->predefinida ?? false);
    }

    public function setPredeterminadaAttribute($value)
    {
        $this->attributes['predefinida'] = (bool) $value;
    }

    public function campos(): HasMany
    {
        return $this->hasMany(PlantillaCampo::class, 'plantilla_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
