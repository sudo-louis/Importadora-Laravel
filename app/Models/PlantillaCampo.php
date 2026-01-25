<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantillaCampo extends Model
{
    protected $table = 'plantilla_campos';

    protected $fillable = [
        'plantilla_id',
        'campo',
        'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }
}
