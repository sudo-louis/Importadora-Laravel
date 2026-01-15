<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'color'    => ['nullable','string','max:50'],
            'permisos' => ['array'],
            'permisos.*' => ['string','max:255'],
        ]);

        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'color' => $data['color'] ?? 'purpura',
                'protegido' => false,
            ]);

            $this->syncPermisos($role, $data['permisos'] ?? []);

            return response()->json(['ok' => true, 'id' => $role->id], 201);
        });
    }

    public function update(Request $request, Role $role)
    {
        if ($role->protegido) {
            return response()->json(['message' => 'Este rol está protegido.'], 403);
        }

        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'color'    => ['nullable','string','max:50'],
            'permisos' => ['array'],
            'permisos.*' => ['string','max:255'],
        ]);

        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'color' => $data['color'] ?? $role->color ?? 'purpura',
            ]);

            $this->syncPermisos($role, $data['permisos'] ?? []);

            return response()->json(['ok' => true]);
        });
    }

    public function destroy(Role $role)
    {
        if ($role->protegido) {
            return back()->with('error', 'Este rol está protegido.');
        }

        // Si hay usuarios usando este rol, decide qué hacer (aquí bloqueamos)
        if ($role->users()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay usuarios asignados a este rol.');
        }

        $role->permisos()->detach();
        $role->delete();

        return back()->with('success', 'Rol eliminado.');
    }

    private function syncPermisos(Role $role, array $permNames): void
    {
        $permNames = collect($permNames)
            ->map(fn($p) => trim((string)$p))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Asegura que existan en tabla permisos
        $existing = Permiso::query()->whereIn('name', $permNames)->get()->keyBy('name');

        $ids = [];
        foreach ($permNames as $name) {
            $perm = $existing[$name] ?? Permiso::create([
                'name' => $name,
                'guard_name' => 'web',
            ]);
            $ids[] = $perm->id;
        }

        $role->permisos()->sync($ids);
    }
}
