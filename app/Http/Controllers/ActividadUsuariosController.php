<?php

namespace App\Http\Controllers;

use App\Models\ActividadLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActividadUsuariosController extends Controller
{
    public function list(Request $request)
    {
        try {
            // Trae usuarios + conteo de actividad (actividad_logs.user_id) + rol (primer rol)
            $users = User::query()
                ->select(['id', 'name', 'email', 'is_active'])
                ->withCount(['actividadLogs as actividades'])
                ->with(['roles:id,name'])
                ->orderByDesc('actividades')
                ->orderBy('name')
                ->limit(200) // puedes subir/bajar si quieres
                ->get();

            $items = $users->map(function (User $u) {
                $roleName = $u->roles->first()?->name;

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'username' => $u->username ?? null, // si no existe en tu BD quedará null
                    'is_active' => (bool) $u->is_active,
                    'role_name' => $roleName,
                    'actividades' => (int) ($u->actividades ?? 0),
                ];
            })->values()->all();

            return response()->json([
                'ok' => true,
                'users' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('ActividadUsuariosController@list ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'users' => [],
                'message' => 'Error cargando usuarios: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(User $user)
    {
        // Calcula rol y actividades para header (el blade lo usa)
        $user->loadMissing('roles:id,name');
        $roleName = $user->roles->first()?->name;

        $actividades = ActividadLog::query()
            ->where('user_id', $user->id)
            ->count();

        $u = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username ?? null,
            'is_active' => (bool) $user->is_active,
            'role_name' => $roleName,
            'actividades' => (int) $actividades,
        ];

        return view('actividad.usuario_detalle', compact('u'));
    }

    public function logs(Request $request, User $user)
    {
        try {
            $data = $request->validate([
                'accion'   => ['nullable', 'string', 'max:50'],
                'desde'    => ['nullable', 'date'],
                'hasta'    => ['nullable', 'date'],
                'page'     => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
            ]);

            $perPage = (int) ($data['per_page'] ?? 20);
            if ($perPage < 5) $perPage = 5;
            if ($perPage > 100) $perPage = 100;

            $q = ActividadLog::query()
                ->with(['contenedor:id,numero_contenedor'])
                ->where('user_id', $user->id)
                ->orderByDesc('fecha_hora');

            if (!empty($data['accion'])) {
                $q->where('accion', strtolower($data['accion']));
            }
            if (!empty($data['desde'])) {
                $q->whereDate('fecha_hora', '>=', $data['desde']);
            }
            if (!empty($data['hasta'])) {
                $q->whereDate('fecha_hora', '<=', $data['hasta']);
            }

            $page = $q->paginate($perPage)->withQueryString();

            $logs = $page->getCollection()->map(function ($l) {
                $cambios = $this->diffKeys(
                    (array) ($l->datos_anteriores ?? []),
                    (array) ($l->datos_nuevos ?? [])
                );

                return [
                    'id' => $l->id,
                    'accion' => $l->accion,
                    'modulo' => $l->modulo,
                    'descripcion' => $l->descripcion,
                    'fecha_hora' => optional($l->fecha_hora)->format('Y-m-d H:i:s'),
                    'fecha' => optional($l->fecha_hora)->format('Y-m-d') ?? '',
                    'hora' => optional($l->fecha_hora)->format('H:i:s') ?? '',
                    'contenedor' => $l->contenedor?->numero_contenedor ?? '',
                    'cambios' => $cambios,
                ];
            })->values()->all();

            return response()->json([
                'ok' => true,
                'logs' => $logs,
                'pagination' => [
                    'current_page' => $page->currentPage(),
                    'last_page' => $page->lastPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                    'from' => $page->firstItem(),
                    'to' => $page->lastItem(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ActividadUsuariosController@logs ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'logs' => [],
                'pagination' => null,
                'message' => 'Error cargando logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function diffKeys(array $before, array $after): array
    {
        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $changed = [];

        foreach ($keys as $k) {
            $b = $before[$k] ?? null;
            $a = $after[$k] ?? null;
            if ($b != $a) $changed[] = $k;
        }

        return $changed;
    }
}
