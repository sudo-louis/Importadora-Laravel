<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Models\Contenedor;
use App\Models\Plantilla;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportesController extends Controller
{
    public function index(Request $request)
    {
        $templates = $this->buildTemplates();

        $filters = [
            'template'     => $request->input('template', 'default:general'),
            'from'         => $request->input('from'),
            'to'           => $request->input('to'),
            'clientes'     => $request->input('clientes', []),
            'contenedores' => $request->input('contenedores', []),
        ];

        $filters['clientes']     = $this->normalizeList($filters['clientes']);
        $filters['contenedores'] = $this->normalizeList($filters['contenedores']);

        $results = [];
        if ($filters['from'] && $filters['to']) {
            $rows = $this->buildQueryFromFilters($filters)->limit(500)->get();
            $results = $rows->map(fn ($c) => $this->toResultRow($c))->values()->all();
        }

        return view('reportes.index', [
            'templates' => $templates,
            'filters'   => $filters,
            'results'   => $results,
        ]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'template' => ['required', 'string'],
            'from'     => ['required', 'date'],
            'to'       => ['required', 'date'],
        ]);

        $filters = [
            'template'     => $request->input('template'),
            'from'         => $request->input('from'),
            'to'           => $request->input('to'),
            'clientes'     => $this->normalizeList($request->input('clientes', [])),
            'contenedores' => $this->normalizeList($request->input('contenedores', [])),
        ];

        [$fields, $title] = $this->fieldsForTemplate($filters['template']);

        if (empty($fields)) {
            return back()->with('error', 'La plantilla seleccionada no tiene campos configurados.');
        }

        $rows = $this->buildQueryFromFilters($filters)->get();
        $filename = $this->safeFilename($title) . '_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new ReporteExport(
                rows: $rows,
                fields: $fields,
                labels: $this->fieldLabels(),
                title: $title
            ),
            $filename
        );
    }

    /**
     * ✅ Autocomplete CLIENTES filtrado por rango (from/to)
     * Query params esperados:
     * - q (string, min 2)
     * - from (date, opcional)
     * - to (date, opcional)
     */
    public function autocompleteClientes(Request $request)
    {
        try {
            $q = $request->query('q', '');
            if (is_array($q)) $q = implode(' ', $q);
            $q = trim((string) $q);

            if (mb_strlen($q) < 2) {
                return response()->json([]);
            }

            $from = $request->query('from');
            $to   = $request->query('to');

            $query = Contenedor::query()
                ->select('cliente')
                ->whereNotNull('cliente')
                ->where('cliente', '!=', '');

            // ✅ aplica rango si viene completo (recomendado)
            if ($from && $to) {
                $fromD = Carbon::parse($from)->startOfDay()->toDateString();
                $toD   = Carbon::parse($to)->endOfDay()->toDateString();
                $query->whereBetween('fecha_llegada', [$fromD, $toD]);
            }

            // Búsqueda por palabras
            $q = preg_replace('/\s+/', ' ', $q);
            $parts = array_values(array_filter(explode(' ', $q), fn ($p) => mb_strlen($p) >= 2));
            foreach ($parts as $p) {
                $query->where('cliente', 'like', '%' . $p . '%');
            }

            $items = $query
                ->groupBy('cliente')
                ->orderBy('cliente')
                ->limit(20)
                ->pluck('cliente')
                ->map(fn ($v) => preg_replace('/\s+/', ' ', trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->values()
                ->all();

            return response()->json($items);
        } catch (\Throwable $e) {
            Log::error('autocompleteClientes failed', [
                'message' => $e->getMessage(),
                'q' => $request->query('q'),
                'from' => $request->query('from'),
                'to' => $request->query('to'),
            ]);
            return response()->json([]);
        }
    }

    /**
     * ✅ Autocomplete CONTENEDORES filtrado por rango (from/to)
     * Query params esperados:
     * - q (string, min 2)
     * - from (date, opcional)
     * - to (date, opcional)
     */
    public function autocompleteContenedores(Request $request)
    {
        try {
            $q = $request->query('q', '');
            if (is_array($q)) $q = implode(' ', $q);
            $q = trim((string) $q);

            if (mb_strlen($q) < 2) {
                return response()->json([]);
            }

            $from = $request->query('from');
            $to   = $request->query('to');

            $query = Contenedor::query()
                ->select('numero_contenedor')
                ->whereNotNull('numero_contenedor')
                ->where('numero_contenedor', '!=', '');

            // ✅ aplica rango si viene completo
            if ($from && $to) {
                $fromD = Carbon::parse($from)->startOfDay()->toDateString();
                $toD   = Carbon::parse($to)->endOfDay()->toDateString();
                $query->whereBetween('fecha_llegada', [$fromD, $toD]);
            }

            $q = preg_replace('/\s+/', ' ', $q);
            $qNoSpaces = str_replace(' ', '', $q);

            $query->where(function ($w) use ($q, $qNoSpaces) {
                $w->where('numero_contenedor', 'like', '%' . $q . '%');
                if ($qNoSpaces !== $q) {
                    $w->orWhere('numero_contenedor', 'like', '%' . $qNoSpaces . '%');
                }
            });

            $items = $query
                ->groupBy('numero_contenedor')
                ->orderBy('numero_contenedor')
                ->limit(20)
                ->pluck('numero_contenedor')
                ->map(fn ($v) => preg_replace('/\s+/', ' ', trim((string) $v)))
                ->filter(fn ($v) => $v !== '')
                ->values()
                ->all();

            return response()->json($items);
        } catch (\Throwable $e) {
            Log::error('autocompleteContenedores failed', [
                'message' => $e->getMessage(),
                'q' => $request->query('q'),
                'from' => $request->query('from'),
                'to' => $request->query('to'),
            ]);
            return response()->json([]);
        }
    }

    // =========================
    // Query con filtros
    // =========================
    private function buildQueryFromFilters(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to   = Carbon::parse($filters['to'])->endOfDay();

        $q = Contenedor::query()
            ->with(['liberacion', 'envioDocumento', 'cotizacion', 'despacho', 'gastos'])
            ->whereBetween('fecha_llegada', [$from->toDateString(), $to->toDateString()])
            ->orderBy('fecha_llegada', 'desc');

        if (!empty($filters['clientes'])) {
            $q->whereIn('cliente', $filters['clientes']);
        }

        if (!empty($filters['contenedores'])) {
            $q->whereIn('numero_contenedor', $filters['contenedores']);
        }

        return $q;
    }

    private function normalizeList($value): array
    {
        if (is_null($value)) return [];

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($v) => trim((string) $v))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values()
                ->all();
        }

        $value = trim((string) $value);
        if ($value === '') return [];

        if (str_contains($value, ',')) {
            return collect(explode(',', $value))
                ->map(fn ($v) => trim($v))
                ->filter(fn ($v) => $v !== '')
                ->unique()
                ->values()
                ->all();
        }

        return [$value];
    }

    private function toResultRow(Contenedor $c): array
    {
        $fechaArribo   = $c->fecha_llegada ? Carbon::parse($c->fecha_llegada)->format('Y-m-d') : '';
        $fechaRegistro = $c->created_at ? Carbon::parse($c->created_at)->format('Y-m-d H:i') : '';

        return [
            'id' => $c->id,
            'numero_contenedor'  => $c->numero_contenedor,
            'cliente'            => $c->cliente,
            'fecha_arribo'       => $fechaArribo,
            'fecha_registro'     => $fechaRegistro,
            'dias_transcurridos' => $c->fecha_llegada ? Carbon::parse($c->fecha_llegada)->diffInDays(now()) : 0,
            'estado'             => $c->estado_label ?? $c->estado,
        ];
    }

    // =========================
    // Templates (default + custom)
    // =========================
    private function buildTemplates()
    {
        $default = $this->defaultTemplates();

        $custom = Plantilla::query()
            ->where('predefinida', false)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'nombre'])
            ->map(fn ($p) => [
                'id'     => $p->id,
                'key'    => "custom_{$p->id}",
                'label'  => $p->nombre,
                'icon'   => '🧩',
                'source' => 'custom',
            ]);

        return collect($default)->merge($custom)->values();
    }

    private function fieldsForTemplate(string $templateKey): array
    {
        if (str_starts_with($templateKey, 'default:')) {
            $def = collect($this->defaultTemplates())->firstWhere('key', $templateKey);
            return [$def['fields'] ?? [], $def['label'] ?? 'Reporte'];
        }

        if (preg_match('/^custom_(\d+)$/', $templateKey, $m)) {
            $id = (int) $m[1];
            $plantilla = Plantilla::query()->with('campos')->find($id);
            if (!$plantilla) return [[], 'Reporte'];

            $fields = $plantilla->campos->pluck('campo')->filter()->values()->all();
            return [$fields, $plantilla->nombre];
        }

        return [[], 'Reporte'];
    }

    private function defaultTemplates(): array
    {
        return [
            [
                'id' => 1, 'key' => 'default:financiero', 'label' => 'Reporte Financiero', 'icon' => '💲', 'source' => 'default',
                'fields' => [
                    'numero_contenedor','cliente','fecha_arribo',
                    'docs.docs_enviados','docs.fecha_envio',
                    'cotizacion.fecha_pago','cotizacion.impuestos','cotizacion.honorarios','cotizacion.maniobras','cotizacion.almacenaje','cotizacion.total',
                    'gastos.gastos_generales','gastos.total_gastos',
                ],
            ],
            [
                'id' => 2, 'key' => 'default:general', 'label' => 'Reporte General', 'icon' => '🧾', 'source' => 'default',
                'fields' => [
                    'numero_contenedor','cliente','fecha_arribo','proveedor','naviera','mercancia_recibida',
                    'estado','fecha_registro','dias_transcurridos',
                ],
            ],
            [
                'id' => 3, 'key' => 'default:trazabilidad', 'label' => 'Reporte de Trazabilidad', 'icon' => '🕒', 'source' => 'default',
                'fields' => [
                    'numero_contenedor','cliente','fecha_arribo','estado',
                    'liberacion.fecha_liberacion','docs.fecha_envio','cotizacion.fecha_pago',
                    'despacho.fecha_modulacion','despacho.fecha_entrega',
                ],
            ],
        ];
    }

    private function fieldLabels(): array
    {
        return [
            'numero_contenedor' => 'Contenedor',
            'cliente' => 'Cliente',
            'fecha_arribo' => 'Fecha Arribo',
            'proveedor' => 'Proveedor',
            'naviera' => 'Naviera',
            'mercancia_recibida' => 'Mercancía',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha Registro',
            'dias_transcurridos' => 'Días Transcurridos',

            'docs.docs_enviados' => 'Docs Enviados',
            'docs.fecha_envio'   => 'Fecha Envío',

            'cotizacion.fecha_pago'   => 'Fecha Pago',
            'cotizacion.impuestos'    => 'Impuestos',
            'cotizacion.honorarios'   => 'Honorarios',
            'cotizacion.maniobras'    => 'Maniobras',
            'cotizacion.almacenaje'   => 'Almacenaje',
            'cotizacion.total'        => 'Total Cotización',

            'liberacion.fecha_liberacion' => 'Fecha Liberación',

            'despacho.fecha_modulacion' => 'Fecha Modulación',
            'despacho.fecha_entrega'    => 'Fecha Entrega',

            'gastos.gastos_generales' => 'Gastos Generales',
            'gastos.total_gastos'     => 'Total Gastos',
        ];
    }

    private function safeFilename(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^\pL\pN\-_ ]/u', '', $name);
        $name = preg_replace('/\s+/', '_', $name);
        return $name ?: 'Reporte';
    }
}
