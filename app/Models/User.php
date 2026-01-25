<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    // 👇 Importante: NO pongas username fijo si no existe la columna.
    // Lo agregamos dinámicamente en el constructor.
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // ✅ compat: si existe columna username, permitir fill
        try {
            if (Schema::hasColumn('users', 'username') && !in_array('username', $this->fillable, true)) {
                $this->fillable[] = 'username';
            }
        } catch (\Throwable $e) {
            // si corre en un contexto sin DB disponible (tests/cli raros), no truena
        }
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id')
            ->withTimestamps();
    }

    // ✅ para actividad: relación directa
    public function actividadLogs(): HasMany
    {
        return $this->hasMany(ActividadLog::class, 'user_id');
    }

    // ===== Permisos (se queda como lo tienes) =====
    public function hasPermiso(string $modulo, ?string $tipo = null): bool
    {
        $modulo = mb_strtolower(trim($modulo));
        $tipo = $tipo !== null ? mb_strtolower(trim($tipo)) : null;

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

    // ✅ útil para actividad (rol primario)
    public function primaryRole()
    {
        return $this->roles()->orderBy('roles.id')->first();
    }
}
