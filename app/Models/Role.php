<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    // ✅ Escenario A
    protected $table = 'roles';

    // ✅ agrega lo que realmente usas
    protected $fillable = ['name', 'color', 'description'];

    public function permisos(): BelongsToMany
    {
        // ✅ pivote: role_permiso
        return $this->belongsToMany(
            Permiso::class,
            'role_permiso',
            'role_id',
            'permiso_id'
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        // ✅ pivote: user_role
        return $this->belongsToMany(
            User::class,
            'user_role',
            'role_id',
            'user_id'
        )->withTimestamps();
    }
}
