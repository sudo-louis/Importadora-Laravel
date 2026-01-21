<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // ✅ Tu BD real
    protected $table = 'roles';

    protected $fillable = ['name', 'color'];

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_role',   // ✅ pivote real
            'role_id',
            'user_id'
        )->withTimestamps();
    }

    public function permisos()
    {
        return $this->belongsToMany(
            Permiso::class,
            'role_permiso', // ✅ pivote real
            'role_id',
            'permiso_id'
        )->withTimestamps();
    }
}
