<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use App\Models\Cotizacion;
use App\Models\Despacho; // ✅ NUEVO
use App\Models\EnvioDocumento;
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
        $mode = $request->query('mode', 'view');
        $tab  = $request->query('tab', 'registro');

        $contenedor->loadMissing([
            'creador',
            'liberacion',
            'gastosLiberacion',
            'envioDocumento',
            'cotizacion',
            'despacho', // ✅ NUEVO
        ]);

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
            $libData = collect($data)->except('gastos')->toArray();
            $lib = $contenedor->liberacion ?: new Liberacion(['contenedor_id' => $contenedor->id]);
            $lib->fill($libData);
            $lib->save();

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

        $impuestos  = (float) ($data['impuestos'] ?? 0);
        $honorarios = (float) ($data['honorarios'] ?? 0);
        $maniobras  = (float) ($data['maniobras'] ?? 0);
        $almacenaje = (float) ($data['almacenaje'] ?? 0);

        // OJO: 'total' es columna generada en MySQL -> NO se guarda desde Laravel
        $cot = $contenedor->cotizacion ?: new Cotizacion(['contenedor_id' => $contenedor->id]);

        $cot->fill([
            'fecha_pago' => $data['fecha_pago'] ?? null,
            'impuestos' => $impuestos,
            'honorarios' => $honorarios,
            'maniobras' => $maniobras,
            'almacenaje' => $almacenaje,
        ]);

        $cot->save();
        $cot->refresh();

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'cotizacion'])
            ->with('success', 'Cotización actualizada');
    }

    /**
     * ✅ NUEVO: Guarda pestaña Despacho
     */
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

        $despacho = $contenedor->despacho ?: new Despacho(['contenedor_id' => $contenedor->id]);
        $despacho->fill($data);
        $despacho->save();

        return redirect()
            ->route('contenedores.show', ['contenedor' => $contenedor->id, 'mode' => 'edit', 'tab' => 'despacho'])
            ->with('success', 'Despacho actualizado');
    }
}
