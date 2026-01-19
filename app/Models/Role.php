<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'role_permiso', 'role_id', 'permiso_id')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role', 'role_id', 'user_id')
            ->withTimestamps();
    }

    // Opción A: Administrador siempre full permisos
    public function isAdmin(): bool
    {
        return mb_strtolower($this->name) === 'administrador';
    }
}
