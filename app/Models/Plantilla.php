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
        'predeterminada',
        'created_by',
    ];

    protected $casts = [
        'predeterminada' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campos(): HasMany
    {
        return $this->hasMany(PlantillaCampo::class, 'plantilla_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
