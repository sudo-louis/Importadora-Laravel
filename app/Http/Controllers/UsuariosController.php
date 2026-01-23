<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'usuarios');

        // ========= USERS =========
        $select = ['id','name','email','created_at','updated_at'];

        // No rompemos si no existe username / is_active
        if (Schema::hasColumn('users', 'username')) $select[] = 'username';
        if (Schema::hasColumn('users', 'is_active')) $select[] = 'is_active';

        $users = User::query()
            ->with(['roles:id,name,color'])
            ->select($select)
            ->get()
            ->map(function ($u) {
                $primaryRole = $u->roles->sortBy('id')->first();

                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'username'   => $u->getAttribute('username'),
                    'is_active'  => (bool) ($u->getAttribute('is_active') ?? true),
                    'role_id'    => $primaryRole?->id,
                    'role_name'  => $primaryRole?->name,
                    'role_color' => $primaryRole?->color,
                ];
            })
            ->values()
            ->all();

        // ========= ROLES simple (para selects) =========
        $roles = Role::query()
            ->select(['id','name','color'])
            ->orderBy('id')
            ->get()
            ->toArray();

        // ========= ROLES FULL (cards) =========
        $rolesFull = Role::query()
            ->with(['permisos:id,modulo,tipo'])
            ->withCount('users')
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                $name = mb_strtolower($r->name ?? '');
                $protegido = in_array($name, ['administrador', 'admin', 'superadmin'], true);

                return [
                    'id'          => $r->id,
                    'name'        => $r->name,
                    'color'       => $r->color,
                    'users_count' => $r->users_count ?? 0,
                    'protegido'   => $protegido,
                    'permisos'    => $r->permisos->map(fn($p) => [
                        'id'     => $p->id,
                        'modulo' => $p->modulo,
                        'tipo'   => $p->tipo,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        // ========= permisosByModulo =========
        $permisosByModulo = Permiso::query()
            ->select(['id','modulo','tipo','name'])
            ->orderBy('modulo')
            ->orderBy('tipo')
            ->get()
            ->groupBy('modulo')
            ->map(function ($items) {
                return $items->map(fn($p) => [
                    'id' => $p->id,
                    'modulo' => $p->modulo,
                    'tipo' => $p->tipo,
                    'name' => $p->name,
                ])->values()->all();
            })
            ->toArray();

        return view('usuarios.index', [
            'tab' => $tab,
            'users' => $users,
            'roles' => $roles,
            'rolesFull' => $rolesFull,
            'permisosByModulo' => $permisosByModulo,
        ]);
    }

    public function data()
    {
        $users = User::query()
            ->with(['roles:id,name,color'])
            ->get()
            ->map(function ($u) {
                $primaryRole = $u->roles->sortBy('id')->first();

                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'username'   => $u->getAttribute('username'),
                    'is_active'  => (bool) ($u->getAttribute('is_active') ?? true),
                    'role_id'    => $primaryRole?->id,
                    'role_name'  => $primaryRole?->name,
                    'role_color' => $primaryRole?->color,
                ];
            })
            ->values()
            ->all();

        return response()->json(['ok' => true, 'users' => $users]);
    }

    // =========================================================
    // ===================== ROLES (FUNCIONAL) ==================
    // =========================================================

    public function rolesStore(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')],
            'color' => ['nullable','string','max:30'],

            'matrix' => ['required','array'],
            'matrix.create_contenedores' => ['nullable','boolean'],
            'matrix.tabs' => ['nullable','array'],
            'matrix.system' => ['nullable','array'],
        ]);

        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name'  => $data['name'],
                'color' => $data['color'] ?? 'Púrpura',
            ]);

            $permisoIds = $this->matrixToPermisoIds($data['matrix'] ?? []);
            $role->permisos()->sync($permisoIds);

            $role->load(['permisos:id,modulo,tipo'])->loadCount('users');

            return response()->json([
                'ok' => true,
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'color' => $role->color,
                    'users_count' => $role->users_count ?? 0,
                    'protegido' => false,
                    'permisos' => $role->permisos->map(fn($p) => [
                        'id' => $p->id,
                        'modulo' => $p->modulo,
                        'tipo' => $p->tipo,
                    ])->values()->all(),
                ]
            ]);
        });
    }

    public function rolesUpdate(Request $request, Role $role)
    {
        $nameLower = mb_strtolower($role->name ?? '');
        if (in_array($nameLower, ['administrador','admin','superadmin'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Este rol está protegido y no se puede modificar.'
            ], 403);
        }

        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')->ignore($role->id)],
            'color' => ['nullable','string','max:30'],

            'matrix' => ['required','array'],
            'matrix.create_contenedores' => ['nullable','boolean'],
            'matrix.tabs' => ['nullable','array'],
            'matrix.system' => ['nullable','array'],
        ]);

        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name'  => $data['name'],
                'color' => $data['color'] ?? $role->color,
            ]);

            $permisoIds = $this->matrixToPermisoIds($data['matrix'] ?? []);
            $role->permisos()->sync($permisoIds);

            $role->load(['permisos:id,modulo,tipo'])->loadCount('users');

            return response()->json([
                'ok' => true,
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'color' => $role->color,
                    'users_count' => $role->users_count ?? 0,
                    'protegido' => false,
                    'permisos' => $role->permisos->map(fn($p) => [
                        'id' => $p->id,
                        'modulo' => $p->modulo,
                        'tipo' => $p->tipo,
                    ])->values()->all(),
                ]
            ]);
        });
    }

    public function rolesDestroy(Role $role)
    {
        $nameLower = mb_strtolower($role->name ?? '');
        if (in_array($nameLower, ['administrador','admin','superadmin'], true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Este rol está protegido y no se puede eliminar.'
            ], 403);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'No puedes eliminar un rol que tiene usuarios asignados.'
            ], 422);
        }

        return DB::transaction(function () use ($role) {
            $role->permisos()->sync([]);
            $role->delete();

            return response()->json(['ok' => true]);
        });
    }

    // =========================================================
    // ===================== USUARIOS (FUNCIONAL) ===============
    // =========================================================

    public function store(Request $request)
    {
        // Validación base (sin romper si no existe username/is_active)
        $rules = [
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('users','email')],
            'role_id' => ['required','integer', 'exists:roles,id'],
            'password' => ['required','string','min:6','confirmed'],
        ];

        if (Schema::hasColumn('users', 'username')) {
            $rules['username'] = ['required','string','max:255', Rule::unique('users','username')];
        } else {
            // si no hay username, no lo pedimos
            $rules['username'] = ['nullable'];
        }

        if (Schema::hasColumn('users', 'is_active')) {
            $rules['is_active'] = ['nullable','boolean'];
        }

        $data = $request->validate($rules);

        return DB::transaction(function () use ($data) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']), // ✅ CLAVE para que pueda iniciar sesión
            ];

            if (Schema::hasColumn('users', 'username')) {
                $payload['username'] = $data['username'] ?? null;
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = array_key_exists('is_active', $data) ? (int)!!$data['is_active'] : 1;
            }

            $user = User::create($payload);

            // asignar rol
            $user->roles()->sync([(int)$data['role_id']]);

            $user->load(['roles:id,name,color']);
            $primaryRole = $user->roles->sortBy('id')->first();

            return response()->json([
                'ok' => true,
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'username'   => $user->getAttribute('username'),
                    'is_active'  => (bool) ($user->getAttribute('is_active') ?? true),
                    'role_id'    => $primaryRole?->id,
                    'role_name'  => $primaryRole?->name,
                    'role_color' => $primaryRole?->color,
                ]
            ]);
        });
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role_id' => ['required','integer', 'exists:roles,id'],
            'password' => ['nullable','string','min:6','confirmed'],
        ];

        if (Schema::hasColumn('users', 'username')) {
            $rules['username'] = ['required','string','max:255', Rule::unique('users','username')->ignore($user->id)];
        } else {
            $rules['username'] = ['nullable'];
        }

        if (Schema::hasColumn('users', 'is_active')) {
            $rules['is_active'] = ['required','boolean'];
        }

        $data = $request->validate($rules);

        return DB::transaction(function () use ($user, $data) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
            ];

            if (Schema::hasColumn('users', 'username')) {
                $payload['username'] = $data['username'] ?? null;
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = (int)!!$data['is_active'];
            }

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']); // ✅ si lo cambias, también hashea
            }

            $user->update($payload);

            $user->roles()->sync([(int)$data['role_id']]);

            $user->load(['roles:id,name,color']);
            $primaryRole = $user->roles->sortBy('id')->first();

            return response()->json([
                'ok' => true,
                'user' => [
                    'id'         => $user->id,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'username'   => $user->getAttribute('username'),
                    'is_active'  => (bool) ($user->getAttribute('is_active') ?? true),
                    'role_id'    => $primaryRole?->id,
                    'role_name'  => $primaryRole?->name,
                    'role_color' => $primaryRole?->color,
                ]
            ]);
        });
    }

    // public function destroy(User $user)
    // {
    //     // ✅ No permitir borrarte a ti mismo
    //     if (auth()->check() && auth()->id() === $user->id) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'No puedes eliminar tu propio usuario.'
    //         ], 403);
    //     }

    //     // ✅ Bloquear eliminación si tiene rol admin
    //     $isAdmin = $user->roles()
    //         ->whereIn(DB::raw('LOWER(name)'), ['administrador','admin','superadmin'])
    //         ->exists();

    //     if ($isAdmin) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'No puedes eliminar usuarios Administradores desde la aplicación.'
    //         ], 403);
    //     }

    //     return DB::transaction(function () use ($user) {
    //         $user->roles()->sync([]); // limpia pivote
    //         $user->delete();

    //         return response()->json(['ok' => true]);
    //     });
    // }

    // =========================================================
    // =================== helpers permisos =====================
    // =========================================================

    private function matrixToPermisoIds(array $matrix): array
    {
        $permisoPairs = [];

        if (!empty($matrix['create_contenedores'])) {
            $permisoPairs[] = ['modulo' => 'contenedores', 'tipo' => 'crear'];
        }

        $tabs = $matrix['tabs'] ?? [];
        $tabKeys = ['registro','liberacion','docs','cotizacion','despacho','gastos'];

        foreach ($tabKeys as $k) {
            $opt = $tabs[$k] ?? 'none';
            foreach ($this->optionToTipos($opt) as $tipo) {
                $permisoPairs[] = ['modulo' => $k, 'tipo' => $tipo];
            }
        }

        $system = $matrix['system'] ?? [];
        $sysKeys = ['reportes','usuarios','actividad'];

        foreach ($sysKeys as $k) {
            if (!empty($system[$k])) {
                foreach (['ver','crear','editar','eliminar'] as $tipo) {
                    $permisoPairs[] = ['modulo' => $k, 'tipo' => $tipo];
                }
            }
        }

        $ids = [];
        foreach ($permisoPairs as $pair) {
            $permiso = Permiso::firstOrCreate(
                ['modulo' => $pair['modulo'], 'tipo' => $pair['tipo']],
                ['name' => $this->permisoName($pair['modulo'], $pair['tipo'])]
            );
            $ids[] = $permiso->id;
        }

        return array_values(array_unique($ids));
    }

    private function optionToTipos(string $opt): array
    {
        $opt = strtolower($opt);
        return match ($opt) {
            'ver' => ['ver'],
            'editar' => ['ver','editar'],
            'total' => ['ver','crear','editar','eliminar'],
            default => [],
        };
    }

    private function permisoName(string $modulo, string $tipo): string
    {
        return strtoupper($modulo) . ' - ' . strtoupper($tipo);
    }
}
