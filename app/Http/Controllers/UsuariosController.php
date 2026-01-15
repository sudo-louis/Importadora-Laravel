<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'usuarios');

        // Roles para selects
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'color' => $r->color,
            ])
            ->values()
            ->all();

        // Usuarios con rol principal
        $users = User::query()
            ->with(['roles:id,name,color'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function (User $u) {
                $role = $u->roles->sortBy('id')->first();
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'is_active' => (bool) $u->is_active,
                    'role_id' => $role?->id,
                    'role_name' => $role?->name,
                    'role_color' => $role?->color,
                ];
            })
            ->values()
            ->all();

        // ✅ Permisos agrupados por módulo (formato listo para Alpine)
        $permisosByModulo = Permiso::query()
            ->orderBy('modulo')
            ->orderBy('tipo')
            ->orderBy('name')
            ->get(['id', 'name', 'modulo', 'tipo'])
            ->groupBy('modulo')
            ->map(function ($items) {
                return $items->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'modulo' => $p->modulo,
                    'tipo' => $p->tipo,
                ])->values()->all();
            })
            ->toArray();

        // Roles con permisos + conteo de usuarios
        $rolesFull = Role::query()
            ->with(['permisos:id,name,modulo,tipo', 'users:id'])
            ->orderBy('name')
            ->get()
            ->map(function (Role $r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'color' => $r->color,
                    'users_count' => $r->users->count(),
                    'permisos' => $r->permisos->map(fn($p) => [
                        'id' => $p->id,
                        'modulo' => $p->modulo,
                        'tipo' => $p->tipo,
                        'name' => $p->name,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return view('usuarios.index', [
            'tab' => $tab,
            'users' => $users,
            'roles' => $roles,
            'rolesFull' => $rolesFull,
            'permisosByModulo' => $permisosByModulo,
        ]);
    }

    // ==========================
    // USUARIOS
    // ==========================
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['nullable','string','max:50', Rule::unique('users', 'username')],
            'email' => ['required','email','max:255', Rule::unique('users', 'email')],
            'password' => ['required','string','min:6','confirmed'],
            'is_active' => ['required', Rule::in([0,1,'0','1',true,false])],
            'role_id' => ['nullable','integer', Rule::exists('roles', 'id')],
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => (bool) $data['is_active'],
            ]);

            if (!empty($data['role_id'])) {
                $user->roles()->sync([(int) $data['role_id']]);
            }
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'usuarios'])
            ->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'username' => ['nullable','string','max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required','email','max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable','string','min:6','confirmed'],
            'is_active' => ['required', Rule::in([0,1,'0','1',true,false])],
            'role_id' => ['nullable','integer', Rule::exists('roles', 'id')],
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->fill([
                'name' => $data['name'],
                'username' => $data['username'] ?? null,
                'email' => $data['email'],
                'is_active' => (bool) $data['is_active'],
            ]);

            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            if (array_key_exists('role_id', $data)) {
                if (!empty($data['role_id'])) {
                    $user->roles()->sync([(int) $data['role_id']]);
                } else {
                    $user->roles()->detach();
                }
            }
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'usuarios'])
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('usuarios.index', ['tab' => 'usuarios'])
                ->with('error', 'No puedes eliminar el usuario con el que tienes la sesión activa.');
        }

        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->delete();
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'usuarios'])
            ->with('success', 'Usuario eliminado correctamente.');
    }

    // ==========================
    // ROLES
    // ==========================
    public function rolesStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('roles', 'name')],
            'color' => ['nullable','string','max:50'],
            'permisos' => ['nullable','array'],
            'permisos.*' => ['integer', Rule::exists('permisos', 'id')],
        ]);

        DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'color' => $data['color'] ?? 'Púrpura',
            ]);

            $role->permisos()->sync($data['permisos'] ?? []);
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', 'Rol creado correctamente.');
    }

    public function rolesUpdate(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'color' => ['nullable','string','max:50'],
            'permisos' => ['nullable','array'],
            'permisos.*' => ['integer', Rule::exists('permisos', 'id')],
        ]);

        DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'color' => $data['color'] ?? $role->color,
            ]);

            $role->permisos()->sync($data['permisos'] ?? []);
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function rolesDestroy(Request $request, Role $role)
    {
        if ($role->users()->exists()) {
            return redirect()
                ->route('usuarios.index', ['tab' => 'roles'])
                ->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        DB::transaction(function () use ($role) {
            $role->permisos()->detach();
            $role->delete();
        });

        return redirect()
            ->route('usuarios.index', ['tab' => 'roles'])
            ->with('success', 'Rol eliminado correctamente.');
    }
}
