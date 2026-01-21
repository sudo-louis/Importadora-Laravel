<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name','email','username','password','is_active',
    ];

    protected $hidden = [
        'password','remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_role',
            'user_id',
            'role_id'
        )->withTimestamps();
    }

    /**
     * Permiso exacto: modulo + tipo (ej: reportes + ver)
     * Si $tipo es null, busca cualquier permiso dentro del módulo.
     */
    public function hasPermiso(string $modulo, ?string $tipo = null): bool
    {
        $modulo = mb_strtolower(trim($modulo));
        $tipo = $tipo !== null ? mb_strtolower(trim($tipo)) : null;

        // Asegura permisos cargados en roles (1 solo load)
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permisos');
        } else {
            $this->roles->each(function ($role) {
                if (!$role->relationLoaded('permisos')) {
                    $role->load('permisos');
                }
            });
        }

        foreach ($this->roles as $role) {
            foreach ($role->permisos as $p) {
                $pModulo = mb_strtolower((string) $p->modulo);
                $pTipo   = mb_strtolower((string) $p->tipo);

                if ($pModulo === $modulo && ($tipo === null || $pTipo === $tipo)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canAccessModule(string $modulo): bool
    {
        return $this->hasPermiso($modulo, null);
    }
}
