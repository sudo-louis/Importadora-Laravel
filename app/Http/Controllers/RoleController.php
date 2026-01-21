<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')],
            'color' => ['nullable','string','max:50'],

            // ✅ soporta tu UI (matrix)
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

            return response()->json(['ok' => true, 'id' => $role->id], 201);
        });
    }

    public function update(Request $request, Role $role)
    {
        // ✅ protegido por nombre (igual que tu UsuariosController)
        $nameLower = mb_strtolower($role->name ?? '');
        if (in_array($nameLower, ['administrador','admin','superadmin'], true)) {
            return response()->json(['message' => 'Este rol está protegido.'], 403);
        }

        $data = $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('roles','name')->ignore($role->id)],
            'color' => ['nullable','string','max:50'],

            'matrix' => ['required','array'],
            'matrix.create_contenedores' => ['nullable','boolean'],
            'matrix.tabs' => ['nullable','array'],
            'matrix.system' => ['nullable','array'],
        ]);

        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name'  => $data['name'],
                'color' => $data['color'] ?? $role->color ?? 'Púrpura',
            ]);

            $permisoIds = $this->matrixToPermisoIds($data['matrix'] ?? []);
            $role->permisos()->sync($permisoIds);

            return response()->json(['ok' => true]);
        });
    }

    public function destroy(Role $role)
    {
        $nameLower = mb_strtolower($role->name ?? '');
        if (in_array($nameLower, ['administrador','admin','superadmin'], true)) {
            return back()->with('error', 'Este rol está protegido.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay usuarios asignados a este rol.');
        }

        $role->permisos()->detach();
        $role->delete();

        return back()->with('success', 'Rol eliminado.');
    }

    // ==========================
    // helpers (igual a tu lógica)
    // ==========================

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
