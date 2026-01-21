<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    // ✅ Tu BD real
    protected $table = 'permisos';

    protected $fillable = ['name', 'modulo', 'tipo'];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permiso', // ✅ pivote real
            'permiso_id',
            'role_id'
        )->withTimestamps();
    }
}
