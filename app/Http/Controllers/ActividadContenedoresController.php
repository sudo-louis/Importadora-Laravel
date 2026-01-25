<?php

namespace App\Http\Controllers;

use App\Models\ActividadLog;
use App\Models\Contenedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActividadContenedoresController extends Controller
{
    /**
     * GET /actividad/contenedores/autocomplete?q=con
     * Respuesta: { ok:true, items:[{id, numero_contenedor, cliente, naviera, fecha_llegada}] }
     */
    public function autocomplete(Request $request)
    {
        try {
            $q = trim((string) $request->query('q', ''));

            if (mb_strlen($q) < 2) {
                return response()->json(['ok' => true, 'items' => []]);
            }

            $items = Contenedor::query()
                ->select(['id', 'numero_contenedor', 'cliente', 'naviera', 'fecha_llegada'])
                ->where('numero_contenedor', 'like', "%{$q}%")
                ->orderBy('numero_contenedor')
                ->limit(10)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'numero_contenedor' => $c->numero_contenedor,
                    'cliente' => $c->cliente,
                    'naviera' => $c->naviera,
                    'fecha_llegada' => optional($c->fecha_llegada)->format('Y-m-d'),
                ])
                ->values()
                ->all();

            return response()->json(['ok' => true, 'items' => $items]);
        } catch (\Throwable $e) {
            Log::error('ActividadContenedoresController@autocomplete ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'items' => [],
                'message' => 'Error en autocomplete: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /actividad/contenedores/search?contenedores[]=CON225&accion=&desde=&hasta=
     * Respuesta:
     * { ok:true, contenedores:[{ id, numero_contenedor, cliente, registrado_texto, logs:[...] }] }
     */
    public function search(Request $request)
    {
        try {
            $data = $request->validate([
                'contenedores' => ['required', 'array', 'min:1'],
                'contenedores.*' => ['string', 'max:255'],

                'accion' => ['nullable', 'string', 'max:50'],
                'desde' => ['nullable', 'date'],
                'hasta' => ['nullable', 'date'],
            ]);

            $numeros = array_values(array_unique(array_filter(array_map('trim', $data['contenedores']))));
            if (count($numeros) === 0) {
                return response()->json(['ok' => true, 'contenedores' => []]);
            }

            $contenedores = Contenedor::query()
                ->with(['creador:id,name', 'creador.roles:id,name,color'])
                ->select(['id', 'numero_contenedor', 'cliente', 'created_at', 'created_by'])
                ->whereIn('numero_contenedor', $numeros)
                ->orderBy('numero_contenedor')
                ->get();

            $ids = $contenedores->pluck('id')->all();
            if (empty($ids)) {
                return response()->json(['ok' => true, 'contenedores' => []]);
            }

            $q = ActividadLog::query()
                // 👇 IMPORTANTÍSIMO: no pedir username si no existe
                ->with(['user:id,name', 'user.roles:id,name,color'])
                ->whereIn('contenedor_id', $ids)
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

            $logsByCont = $q->get()->groupBy('contenedor_id');

            $out = [];
            foreach ($contenedores as $c) {
                $logs = ($logsByCont[$c->id] ?? collect())->map(function ($l) {
                    $cambios = $this->diffKeys(
                        (array) ($l->datos_anteriores ?? []),
                        (array) ($l->datos_nuevos ?? [])
                    );

                    $roleName = optional($l->user?->roles?->sortBy('id')->first())->name;

                    return [
                        'id' => $l->id,
                        'accion' => $l->accion,
                        'modulo' => $l->modulo,
                        'descripcion' => $l->descripcion,
                        'fecha_hora' => optional($l->fecha_hora)->format('Y-m-d H:i:s'),

                        'user_name' => $l->user?->name ?? 'Usuario',
                        'role_name' => $roleName,

                        'cambios' => $cambios,
                    ];
                })->values()->all();

                $creadorRole = optional($c->creador?->roles?->sortBy('id')->first())->name ?? 'Usuario';

                $registradoTexto = 'Registrado: ' . ($c->created_at?->format('Y-m-d H:i') ?? '')
                    . ' • ' . ($c->creador?->name ? ($c->creador->name . ' (' . $creadorRole . ')') : '');

                $out[] = [
                    'id' => $c->id,
                    'numero_contenedor' => $c->numero_contenedor,
                    'cliente' => $c->cliente,
                    'registrado_texto' => $registradoTexto,
                    'logs' => $logs,
                ];
            }

            return response()->json(['ok' => true, 'contenedores' => $out]);
        } catch (\Throwable $e) {
            Log::error('ActividadContenedoresController@search ERROR', [
                'msg' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'contenedores' => [],
                'message' => 'Error en search: ' . $e->getMessage(),
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
