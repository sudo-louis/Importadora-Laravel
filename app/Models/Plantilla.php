<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plantilla extends Model
{
    protected $table = 'plantillas';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'predefinida',
        'created_by',
    ];

    protected $casts = [
        'predefinida' => 'boolean',
    ];

    public function campos(): HasMany
    {
        return $this->hasMany(PlantillaCampo::class, 'plantilla_id')->orderBy('orden');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
