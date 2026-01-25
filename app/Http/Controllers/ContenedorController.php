<?php

namespace App\Http\Controllers;

use App\Models\ActividadLog;
use App\Models\Contenedor;
use App\Models\Cotizacion;
use App\Models\Despacho;
use App\Models\EnvioDocumento;
use App\Models\Gasto;
use App\Models\Liberacion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ContenedorController extends Controller
{
    public function index(Request $request)
    {
        $q = Contenedor::query()->with('creador')->latest();

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($qq) use ($s) {
                $qq->where('numero_contenedor', 'like', "%{$s}%")
                    ->orWhere('cliente', 'like', "%{$s}%")
                    ->orWhere('naviera', 'like', "%{$s}%");
            });
        }

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $q->where('estado', $request->estado);
        }

        if ($request->filled('desde')) {
            $q->whereDate('fecha_llegada', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $q->whereDate('fecha_llegada', '<=', $request->hasta);
        }

        $contenedores = $q->paginate(9)->withQueryString();
        return view('contenedores.index', compact('contenedores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_contenedor' => ['required','string','max:255'],
            'cliente' => ['required','string','max:255'],
            'fecha_llegada' => ['required','date'],
            'proveedor' => ['required','string','max:255'],
            'naviera' => ['required','string','max:255'],
            'mercancia_recibida' => ['required','string','max:255'],
        ]);

        $data['estado'] = 'pendiente';
        $data['created_by'] = auth()->id();

        $contenedor = null;

        DB::transaction(function () use ($data, &$contenedor) {
            $contenedor = Contenedor::create($data);

            $this->logActividad(
                $contenedor,
                'crear',
                'registro',
                'Contenedor creado con información inicial',
                [],
                $this->onlyKeys($contenedor->toArray(), [
                    'numero_contenedor','cliente','fecha_llegada','proveedor','naviera','mercancia_recibida','estado'
                ])
            );
        });

        return redirect()
            ->route('contenedores.index')
            ->with('success', 'Contenedor registrado correctamente');
    }

    public function show(Request $request, Contenedor $contenedor)
    {
        $mode = $request->query('mode', 'view');
        $tab  = $request->query('tab', 'registro');

        $contenedor->loadMissing([
            'creador',
            'liberacion',
            'gastosLiberacion',
            'envioDocumento',
            'cotizacion',
            'despacho',
            'gastos',
        ]);

        // ✅ OPCIONAL: si quieres registrar "ver", descomenta esto.
        /*
        $this->logActividad(
            $contenedor,
            'ver',
            'registro',
            'Visualización del contenedor',
            [],
            []
        );
        */

        return view('contenedores.show', compact('contenedor', 'mode', 'tab'));
    }

    public function updateLiberacion(Request $request, Contenedor $contenedor)
    {
        $data = $request->validate([
            'naviera' => ['nullable','string','max:255'],
            'dias_libres' => ['nullable','integer','min:0','max:365'],

            'revalidacion' => ['nullable','boolean'],
            'fecha_revalidacion' => ['nullable','date'],

            'costo_liberacion' => ['nullable','numeric','min:0'],
            'fecha_liberacion' => ['nullable','date'],

            'garantia' => ['nullable','numeric','min:0'],
            'fecha_garantia' => ['nullable','date'],

            'devolucion_garantia' => ['nullable','in:pendiente,entregado'],

            'costos_demora' => ['nullable','numeric','min:0'],
            'fecha_demora' => ['nullable','date'],

            'flete_maritimo' => ['nullable','numeric','min:0'],
            'fecha_flete' => ['nullable','date'],

            'gastos' => ['nullable','array'],
            'gastos.*.descripcion' => ['nullable','string','max:255'],
            'gastos.*.monto' => ['nullable','numeric','min:0'],
        ]);

        $data['revalidacion'] = (bool) ($request->input('revalidacion', false));
        if (!$data['revalidacion']) $data['fecha_revalidacion'] = null;

        DB::transaction(function () use ($contenedor, $data) {

            $before = $contenedor->liberacion
                ? $contenedor->liberacion->toArray()
                : [];

            $libData = collect($data)->except('gastos')->toArray();
            $lib = $contenedor->liberacion ?: new Liberacion(['contenedor_id' => $contenedor->id]);
            $lib->fill($libData);
            $lib->save();

            // gastos liberación
            $contenedor->gastosLiberacion()->delete();

            $gastos = $data['gastos'] ?? [];
            foreach ($gastos as $g) {
                $desc = trim((string)($g['descripcion'] ?? ''));
                $monto = $g['monto'] ?? null;

                if ($desc === '' && ($monto === null || $monto === '')) continue;

                Gasto::create([
                    'contenedor_id' => $contenedor->id,
                    'tipo' => 'liberacion',
                    'descripcion' => $desc !== '' ? $desc : 'Gasto',
                    'monto' => $monto ?? 0,
                ]);
            }

            $after = $lib->fresh()->toArray();

            $this->logActividad(
                $contenedor,
                'editar',
                'liberacion',
                'Actualización de pestaña Liberación',
                $before,
                $after
            );
        });

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'liberacion'])
            ->with('success', 'Liberación actualizada');
    }

    public function updateEnvioDocumentos(Request $request, Contenedor $contenedor)
    {
        $data = $request->validate([
            'enviado' => ['nullable','boolean'],
            'fecha_envio' => ['nullable','date'],
        ]);

        $beforeDoc = $contenedor->envioDocumento
            ? $this->onlyKeys($contenedor->envioDocumento->toArray(), ['enviado','fecha_envio'])
            : ['enviado' => null, 'fecha_envio' => null];

        $enviado = (bool) ($request->input('enviado', false));

        if (!$enviado) {
            $data['fecha_envio'] = null;
        } else {
            if (empty($data['fecha_envio'])) {
                $data['fecha_envio'] = now()->toDateString();
            }
        }

        $doc = $contenedor->envioDocumento ?: new EnvioDocumento(['contenedor_id' => $contenedor->id]);
        $doc->fill([
            'enviado' => $enviado,
            'fecha_envio' => $data['fecha_envio'] ?? null,
        ]);
        $doc->save();

        $afterDoc = $this->onlyKeys($doc->fresh()->toArray(), ['enviado','fecha_envio']);

        $this->logActividad(
            $contenedor,
            'editar',
            'docs',
            'Actualización de pestaña Envío de Docs',
            $beforeDoc,
            $afterDoc
        );

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'docs'])
            ->with('success', 'Envío de documentos actualizado');
    }

    public function updateCotizacion(Request $request, Contenedor $contenedor)
    {
        $data = $request->validate([
            'fecha_pago' => ['nullable', 'date'],
            'impuestos' => ['nullable', 'numeric', 'min:0'],
            'honorarios' => ['nullable', 'numeric', 'min:0'],
            'maniobras' => ['nullable', 'numeric', 'min:0'],
            'almacenaje' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cot = $contenedor->cotizacion;

        $before = $cot ? $this->onlyKeys($cot->toArray(), [
            'fecha_pago','impuestos','honorarios','maniobras','almacenaje'
        ]) : [];

        $impuestos  = (float) ($data['impuestos'] ?? 0);
        $honorarios = (float) ($data['honorarios'] ?? 0);
        $maniobras  = (float) ($data['maniobras'] ?? 0);
        $almacenaje = (float) ($data['almacenaje'] ?? 0);

        $cot = $cot ?: new Cotizacion(['contenedor_id' => $contenedor->id]);
        $cot->fill([
            'fecha_pago' => $data['fecha_pago'] ?? null,
            'impuestos' => $impuestos,
            'honorarios' => $honorarios,
            'maniobras' => $maniobras,
            'almacenaje' => $almacenaje,
        ]);
        $cot->save();

        $after = $this->onlyKeys($cot->fresh()->toArray(), [
            'fecha_pago','impuestos','honorarios','maniobras','almacenaje'
        ]);

        $this->logActividad(
            $contenedor,
            'editar',
            'cotizacion',
            'Actualización de pestaña Cotización',
            $before,
            $after
        );

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'cotizacion'])
            ->with('success', 'Cotización actualizada');
    }

    public function updateDespacho(Request $request, Contenedor $contenedor)
    {
        $data = $request->validate([
            'numero_pedimento' => ['nullable','string','max:255'],
            'clave_pedimento'  => ['nullable','string','max:10'],
            'importador'       => ['nullable','string','max:255'],

            'tipo_carga' => ['nullable','in:terrestre,maritimo,ferrocarril,aereo'],

            'fecha_carga'             => ['nullable','date'],
            'reconocimiento_aduanero' => ['nullable','date'],
            'fecha_pago'              => ['nullable','date'],
            'fecha_modulacion'        => ['nullable','date'],
            'fecha_entrega'           => ['nullable','date'],
        ]);

        $desp = $contenedor->despacho;

        $before = $desp ? $desp->toArray() : [];

        $despacho = $desp ?: new Despacho(['contenedor_id' => $contenedor->id]);
        $despacho->fill($data);
        $despacho->save();

        $after = $despacho->fresh()->toArray();

        $this->logActividad(
            $contenedor,
            'editar',
            'despacho',
            'Actualización de pestaña Despacho',
            $before,
            $after
        );

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'despacho'])
            ->with('success', 'Despacho actualizado');
    }

    public function updateGastos(Request $request, Contenedor $contenedor)
    {
        $data = $request->validate([
            'gastos' => ['nullable','array'],
            'gastos.*.id' => ['nullable','integer'],
            'gastos.*.descripcion' => ['nullable','string','max:255'],
            'gastos.*.monto' => ['nullable','numeric','min:0'],
        ]);

        DB::transaction(function () use ($contenedor, $data) {

            // BEFORE: snapshot simple
            $before = $contenedor->gastos()
                ->orderBy('id')
                ->get()
                ->map(fn($g) => $this->onlyKeys($g->toArray(), ['id','descripcion','monto']))
                ->values()
                ->all();

            $actuales = $contenedor->gastos()->get();
            $actualIds = $actuales->pluck('id')->all();

            $rows = $data['gastos'] ?? [];
            $keepIds = [];

            foreach ($rows as $g) {
                $id = $g['id'] ?? null;
                $desc = trim((string)($g['descripcion'] ?? ''));
                $monto = $g['monto'] ?? null;

                if ($desc === '' && ($monto === null || $monto === '')) continue;

                $payload = [
                    'contenedor_id' => $contenedor->id,
                    'tipo' => 'general',
                    'descripcion' => $desc !== '' ? $desc : 'Gasto',
                    'monto' => $monto ?? 0,
                ];

                if ($id && in_array($id, $actualIds, true)) {
                    Gasto::where('id', $id)
                        ->where('contenedor_id', $contenedor->id)
                        ->where('tipo', 'general')
                        ->update([
                            'descripcion' => $payload['descripcion'],
                            'monto' => $payload['monto'],
                        ]);

                    $keepIds[] = $id;
                } else {
                    $nuevo = Gasto::create($payload);
                    $keepIds[] = $nuevo->id;
                }
            }

            $toDelete = array_diff($actualIds, $keepIds);

            if (!empty($toDelete)) {
                Gasto::where('contenedor_id', $contenedor->id)
                    ->where('tipo', 'general')
                    ->whereIn('id', $toDelete)
                    ->delete();
            }

            // AFTER
            $after = $contenedor->gastos()
                ->orderBy('id')
                ->get()
                ->map(fn($g) => $this->onlyKeys($g->toArray(), ['id','descripcion','monto']))
                ->values()
                ->all();

            $this->logActividad(
                $contenedor,
                'editar',
                'gastos',
                'Actualización de pestaña Gastos',
                ['gastos' => $before],
                ['gastos' => $after]
            );
        });

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'gastos'])
            ->with('success', 'Gastos actualizados');
    }

    public function destroy(Contenedor $contenedor)
    {
        DB::transaction(function () use ($contenedor) {
            // BEFORE snapshot (mínimo)
            $before = $this->onlyKeys($contenedor->toArray(), [
                'numero_contenedor','cliente','fecha_llegada','proveedor','naviera','mercancia_recibida','estado'
            ]);

            // Si tus FK tienen ON DELETE CASCADE, esto no estorba, pero es seguro.
            $contenedor->gastosLiberacion()?->delete();
            $contenedor->gastos()?->delete();
            $contenedor->liberacion()?->delete();
            $contenedor->envioDocumento()?->delete();
            $contenedor->cotizacion()?->delete();
            $contenedor->despacho()?->delete();

            $this->logActividad(
                $contenedor,
                'eliminar',
                'registro',
                'Contenedor eliminado',
                $before,
                []
            );

            $contenedor->delete();
        });

        return redirect()
            ->route('contenedores.index')
            ->with('success', 'Contenedor eliminado correctamente');
    }

    // =========================
    // Helpers de actividad
    // =========================

    private function logActividad(
        Contenedor $contenedor,
        string $accion,
        string $modulo,
        string $descripcion,
        array $before = [],
        array $after = []
    ): void {
        $userId = auth()->id();
        if (!$userId) return;

        // No guardamos "editar" si no cambió nada
        if (strtolower($accion) === 'editar' && $before == $after) return;

        ActividadLog::create([
            'user_id' => $userId,
            'contenedor_id' => $contenedor->id,
            'accion' => strtolower($accion),
            'modulo' => strtolower($modulo),
            'descripcion' => $descripcion,
            'datos_anteriores' => $before,
            'datos_nuevos' => $after,
            'fecha_hora' => now(),
        ]);
    }

    private function onlyKeys(array $data, array $keys): array
    {
        return Arr::only($data, $keys);
    }
}
