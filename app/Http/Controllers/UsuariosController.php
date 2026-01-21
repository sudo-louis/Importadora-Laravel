<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'usuarios');

        // ========= USERS (para tu pestaña usuarios, sin romper) =========
        // OJO: si tu tabla users no tiene "username", esto no truena porque usamos getAttribute.
        $users = User::query()
            ->with(['roles:id,name,color'])
            ->select(['id','name','email','is_active','created_at','updated_at']) // agrega username si existe en tu tabla
            ->get()
            ->map(function ($u) {
                $primaryRole = $u->roles->sortBy('id')->first();

                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'username'   => $u->getAttribute('username'), // null si no existe
                    'is_active'  => (bool) $u->is_active,
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

        // ========= ROLES FULL (para cards en pestaña roles) =========
        $rolesFull = Role::query()
            ->with(['permisos:id,modulo,tipo']) // relación Role::permisos()
            ->withCount('users') // relación Role::users()
            ->orderBy('id')
            ->get()
            ->map(function ($r) {
                // "protegido" sin columna DB (opción A sin cambios DB):
                // marcamos protegido por nombre (ajústalo si quieres)
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

        // ========= permisosByModulo (por si lo ocupas) =========
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

    /**
     * Si en tu vista planeas pedir data por fetch para usuarios
     */
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
                    'is_active'  => (bool) $u->is_active,
                    'role_id'    => $primaryRole?->id,
                    'role_name'  => $primaryRole?->name,
                    'role_color' => $primaryRole?->color,
                ];
            })
            ->values()
            ->all();

        return response()->json(['users' => $users]);
    }

    // =========================================================
    // ===================== ROLES (FUNCIONAL) ==================
    // =========================================================

    public function rolesStore(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')],
            'color' => ['nullable','string','max:30'],

            // payload de tu UI:
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

            // regresamos el rol “full” ya armado
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
        // proteger admin sin columna
        $nameLower = mb_strtolower($role->name ?? '');
        if (in_array($nameLower, ['administrador','admin','superadmin'], true)) {
            // puedes permitir editar permisos si quieres; aquí lo bloqueo completo:
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
            $role->permisos()->sync([]); // limpia pivote
            $role->delete();

            return response()->json(['ok' => true]);
        });
    }

    /**
     * Convierte el roleMatrix de tu UI (create_contenedores + tabs + system)
     * a IDs reales de permisos en tabla `permisos`.
     */
    private function matrixToPermisoIds(array $matrix): array
    {
        $permisoPairs = [];

        // 1) Crear contenedores
        if (!empty($matrix['create_contenedores'])) {
            $permisoPairs[] = ['modulo' => 'contenedores', 'tipo' => 'crear'];
        }

        // 2) Tabs contenedores
        $tabs = $matrix['tabs'] ?? [];
        $tabKeys = ['registro','liberacion','docs','cotizacion','despacho','gastos'];

        foreach ($tabKeys as $k) {
            $opt = $tabs[$k] ?? 'none';
            foreach ($this->optionToTipos($opt) as $tipo) {
                $permisoPairs[] = ['modulo' => $k, 'tipo' => $tipo];
            }
        }

        // 3) módulos del sistema
        $system = $matrix['system'] ?? [];
        $sysKeys = ['reportes','usuarios','actividad'];

        foreach ($sysKeys as $k) {
            if (!empty($system[$k])) {
                foreach (['ver','crear','editar','eliminar'] as $tipo) {
                    $permisoPairs[] = ['modulo' => $k, 'tipo' => $tipo];
                }
            }
        }

        // Crear permisos si no existen y devolver IDs
        $ids = [];
        foreach ($permisoPairs as $pair) {
            $permiso = Permiso::firstOrCreate(
                ['modulo' => $pair['modulo'], 'tipo' => $pair['tipo']],
                [
                    'name' => $this->permisoName($pair['modulo'], $pair['tipo']),
                ]
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

    // =========================================================
    // ============ USUARIOS (los dejo seguros) =================
    // =========================================================

    public function store(Request $request)
    {
        // luego lo conectamos; por ahora no rompe
        return response()->json(['ok' => false, 'message' => 'Pendiente conectar usuarios.store'], 501);
    }

    public function update(Request $request, User $user)
    {
        return response()->json(['ok' => false, 'message' => 'Pendiente conectar usuarios.update'], 501);
    }

    public function destroy(User $user)
    {
        return response()->json(['ok' => false, 'message' => 'Pendiente conectar usuarios.destroy'], 501);
    }
}
