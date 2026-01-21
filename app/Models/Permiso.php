<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    // ✅ Escenario A
    protected $table = 'permisos';

    protected $fillable = [
        'name',
        'modulo',
        'tipo',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permiso',
            'permiso_id',
            'role_id'
        )->withTimestamps();
    }
}
