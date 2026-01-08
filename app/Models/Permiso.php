<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    protected $fillable = ['name', 'modulo', 'tipo'];

    // protected $table = 'permisos';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permiso', 'permiso_id', 'role_id')
            ->withTimestamps();
    }
}
