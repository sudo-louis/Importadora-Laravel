<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'username',     // ✅ IMPORTANTE (tu UI lo usa)
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password','remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function primaryRole(): ?Role
    {
        return $this->roles()->orderBy('roles.id')->first();
    }

    public function permisos(): \Illuminate\Support\Collection
    {
        return $this->roles()
            ->with('permisos')
            ->get()
            ->pluck('permisos')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function canDo(string $modulo, string $tipo): bool
    {
        return $this->roles()
            ->whereHas('permisos', fn($q) => $q->where('modulo', $modulo)->where('tipo', $tipo))
            ->exists();
    }

    public function plantillas(): HasMany
    {
        return $this->hasMany(\App\Models\Plantilla::class, 'created_by');
    }
}
