<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use App\Models\Gasto;
use App\Models\Liberacion;
use Illuminate\Http\Request;
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

        Contenedor::create($data);

        return redirect()
            ->route('contenedores.index')
            ->with('success', 'Contenedor registrado correctamente');
    }

    public function show(Request $request, Contenedor $contenedor)
    {
        $mode = $request->query('mode', 'view'); // view|edit
        $tab  = $request->query('tab', 'registro');

        $contenedor->loadMissing([
            'creador',
            'liberacion',
            'gastosLiberacion',
        ]);

        return view('contenedores.show', compact('contenedor', 'mode', 'tab'));
    }

    /**
     * Guarda pestaña: Liberación + gastos adicionales.
     */
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

        // Normaliza checkbox
        $data['revalidacion'] = (bool) ($request->input('revalidacion', false));

        // Si no hay revalidación, borra fecha_revalidacion
        if (!$data['revalidacion']) {
            $data['fecha_revalidacion'] = null;
        }

        DB::transaction(function () use ($contenedor, $data) {

            // Upsert liberación
            $libData = collect($data)->except('gastos')->toArray();
            $lib = $contenedor->liberacion ?: new Liberacion(['contenedor_id' => $contenedor->id]);
            $lib->fill($libData);
            $lib->save();

            // Reemplaza gastos tipo liberacion
            $contenedor->gastosLiberacion()->delete();

            $gastos = $data['gastos'] ?? [];
            foreach ($gastos as $g) {
                $desc = trim((string)($g['descripcion'] ?? ''));
                $monto = $g['monto'] ?? null;

                // evita guardar filas vacías
                if ($desc === '' && ($monto === null || $monto === '')) continue;

                Gasto::create([
                    'contenedor_id' => $contenedor->id,
                    'tipo' => 'liberacion',
                    'descripcion' => $desc !== '' ? $desc : 'Gasto',
                    'monto' => $monto ?? 0,
                ]);
            }
        });

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'liberacion'])
            ->with('success', 'Liberación actualizada');
    }

    public function update(Request $request, Contenedor $contenedor)
    {
        return redirect()
            ->route('contenedores.show', $contenedor);
    }

    public function destroy(Contenedor $contenedor)
    {
        $contenedor->delete();

        return redirect()
            ->route('contenedores.index')
            ->with('success', 'Contenedor eliminado');
    }
}
