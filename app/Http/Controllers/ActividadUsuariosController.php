<?php

namespace App\Http\Controllers;

use App\Models\ActividadLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActividadUsuariosController extends Controller
{
    /**
     * GET /actividad/usuarios/data  (alias de /list)
     * Devuelve usuarios con conteo de actividades + rol (si tiene)
     *
     * Respuesta:
     * { ok:true, items:[{id,name,email,username,is_active,role_name,actividades_count}] }
     */
    public function list(Request $request)
    {
        try {
            // OJO: tu tabla users NO tiene username (por eso tronaba antes).
            // Aquí NO lo seleccionamos. Si el frontend lo pide, lo enviamos null.
            $users = User::query()
                ->select(['id', 'name', 'email', 'is_active', 'created_at'])
                // necesita relación actividadLogs en el modelo User (una sola vez)
                ->withCount(['actividadLogs as actividades_count'])
                ->with(['roles:id,name']) // para sacar role_name
                ->orderByDesc('actividades_count')
                ->orderBy('name')
                ->limit(60)
                ->get();

            $items = $users->map(function (User $u) {
                $roleName = $u->roles->first()?->name; // primer rol (si existe)

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'username' => null, // <- tu BD no lo tiene; evitamos error y mantenemos API estable
                    'is_active' => (bool) $u->is_active,
                    'role_name' => $roleName,
                    'actividades_count' => (int) ($u->actividades_count ?? 0),
                ];
            })->values()->all();

            return response()->json([
                'ok' => true,
                'items' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error('ActividadUsuariosController@list ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'items' => [],
                'message' => 'Error cargando usuarios: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /actividad/usuarios/{user}/logs?accion=&desde=&hasta=
     *
     * Respuesta:
     * { ok:true, user:{...}, logs:[...] }
     */
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
                    'contenedor' => $l->contenedor?->numero_contenedor,
                    'cambios' => $cambios,
                ];
            })->values()->all();

            // info del user para header (tu UI puede mostrar role, etc.)
            $user->loadMissing('roles:id,name');
            $roleName = $user->roles->first()?->name;

            return response()->json([
                'ok' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => null, // tu BD no lo tiene
                    'is_active' => (bool) $user->is_active,
                    'role_name' => $roleName,
                ],
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
