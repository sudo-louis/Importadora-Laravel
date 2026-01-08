<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    // Si tu tabla tiene otro nombre, ajusta aquí:
    // protected $table = 'roles';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id')
            ->withTimestamps();
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'role_permiso', 'role_id', 'permiso_id')
            ->withTimestamps();
    }
}
