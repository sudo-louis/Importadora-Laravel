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
            $users = User::query()
                ->select(['id', 'name', 'email', 'is_active'])
                ->withCount(['actividadLogs as actividades_count'])
                ->with(['roles:id,name'])
                ->orderByDesc('actividades_count')
                ->orderBy('name')
                ->limit(200)
                ->get();

            $mapped = $users->map(function (User $u) {
                $roleName = $u->roles->first()?->name;

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'username' => $u->username ?? null, // si existe en tu tabla, lo manda; si no, null
                    'is_active' => (bool) $u->is_active,
                    'role_name' => $roleName,
                    'actividades' => (int) ($u->actividades_count ?? 0),
                ];
            })->values()->all();

            return response()->json([
                'ok' => true,
                'users' => $mapped,
                'items' => $mapped,
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
                'items' => [],
                'message' => 'Error cargando usuarios: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(User $user)
    {
        $user->loadMissing('roles:id,name');

        $roleName = $user->roles->first()?->name;

        $actividades = ActividadLog::where('user_id', $user->id)->count();

        $u = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username ?? null,
            'is_active' => (bool) $user->is_active,
            'role_name' => $roleName,
            'actividades' => (int) $actividades,
        ];

        // ✅ aquí está la corrección clave
        return view('actividad.usuario_detalle', compact('u'));
    }

    public function logs(Request $request, User $user)
    {
        try {
            $data = $request->validate([
                'accion' => ['nullable', 'string', 'max:50'],
                'desde'  => ['nullable', 'date'],
                'hasta'  => ['nullable', 'date'],
            ]);

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

            $logs = $q->limit(500)->get()->map(function ($l) {
                $fechaHora = $l->fecha_hora ? $l->fecha_hora->format('Y-m-d H:i:s') : null;

                $cambios = $this->diffKeys(
                    (array) ($l->datos_anteriores ?? []),
                    (array) ($l->datos_nuevos ?? [])
                );

                return [
                    'id' => $l->id,
                    'accion' => $l->accion,
                    'modulo' => $l->modulo,
                    'descripcion' => $l->descripcion,
                    'fecha_hora' => $fechaHora,

                    // ✅ campos que tu tabla usa:
                    'fecha' => $l->fecha_hora?->format('Y-m-d'),
                    'hora'  => $l->fecha_hora?->format('H:i:s'),

                    'contenedor' => $l->contenedor?->numero_contenedor ?? '-',
                    'cambios' => $cambios,
                ];
            })->values()->all();

            return response()->json([
                'ok' => true,
                'logs' => $logs,
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
