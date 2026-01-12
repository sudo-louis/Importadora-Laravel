<?php

namespace App\Exports;

use App\Models\Contenedor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReporteExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        private Collection $rows,
        private array $fields,
        private array $labels,
        private string $title = 'Reporte'
    ) {}

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_map(fn($k) => $this->labels[$k] ?? $k, $this->fields);
    }

    public function map($c): array
    {
        /** @var Contenedor $c */
        $out = [];
        foreach ($this->fields as $key) {
            $out[] = $this->resolveField($c, $key);
        }
        return $out;
    }

    private function resolveField(Contenedor $c, string $key)
    {
        $date = fn($v) => $v ? Carbon::parse($v)->format('Y-m-d') : '';
        $dt   = fn($v) => $v ? Carbon::parse($v)->format('Y-m-d H:i') : '';
        $num  = fn($v) => is_null($v) ? '' : (float)$v;

        $lib  = $c->liberacion;
        $docs = $c->envioDocumento;
        $cot  = $c->cotizacion;
        $des  = $c->despacho;

        $gastosAll = $c->relationLoaded('gastos') ? $c->gastos : collect();
        $gastosLiberacion = $gastosAll->where('tipo', 'liberacion');
        $gastosGenerales  = $gastosAll->where('tipo', '!=', 'liberacion');

        // ✅ Normaliza keys que vengan como "grupo.campo"
        // Ej: "docs.fecha_envio" => group=docs, field=fecha_envio
        $group = null;
        $field = $key;

        if (str_contains($key, '.')) {
            [$group, $field] = explode('.', $key, 2);
            $group = trim((string)$group);
            $field = trim((string)$field);
        }

        // =========================
        // 1) Keys con prefijo
        // =========================
        if ($group === 'docs') {
            return match ($field) {
                'docs_enviados', 'enviado' => is_null($docs?->enviado) ? '' : ($docs->enviado ? 'Sí' : 'No'),
                'fecha_envio'               => $date($docs?->fecha_envio),
                default                     => '',
            };
        }

        if ($group === 'cotizacion') {
            return match ($field) {
                'fecha_pago'   => $date($cot?->fecha_pago),
                'impuestos'    => $num($cot?->impuestos),
                'honorarios'   => $num($cot?->honorarios),
                'maniobras'    => $num($cot?->maniobras),
                'almacenaje'   => $num($cot?->almacenaje),
                'total'        => $num($cot?->total ?? null),
                default        => '',
            };
        }

        if ($group === 'liberacion') {
            return match ($field) {
                'dias_libres'         => $lib?->dias_libres ?? '',
                'revalidacion'        => is_null($lib?->revalidacion) ? '' : ($lib->revalidacion ? 'Sí' : 'No'),
                'fecha_revalidacion'  => $date($lib?->fecha_revalidacion),
                'costo_liberacion'    => $num($lib?->costo_liberacion),
                'fecha_liberacion'    => $date($lib?->fecha_liberacion),
                'garantia'            => $num($lib?->garantia),
                'fecha_garantia'      => $date($lib?->fecha_garantia),
                'devolucion_garantia' => (string)($lib?->devolucion_garantia ?? ''),
                'costos_demora'       => $num($lib?->costos_demora),
                'fecha_demora'        => $date($lib?->fecha_demora),
                'flete_maritimo'      => $num($lib?->flete_maritimo),
                'fecha_flete'         => $date($lib?->fecha_flete),
                default               => '',
            };
        }

        if ($group === 'despacho') {
            return match ($field) {
                'numero_pedimento'        => (string)($des?->numero_pedimento ?? ''),
                'clave_pedimento'         => (string)($des?->clave_pedimento ?? ''),
                'importador'              => (string)($des?->importador ?? ''),
                'tipo_carga'              => (string)($des?->tipo_carga ?? ''),
                'fecha_carga'             => $date($des?->fecha_carga),
                'reconocimiento_aduanero' => $date($des?->reconocimiento_aduanero),
                'fecha_pago'              => $date($des?->fecha_pago),
                'fecha_modulacion'        => $date($des?->fecha_modulacion),
                'fecha_entrega'           => $date($des?->fecha_entrega),
                default                   => '',
            };
        }

        if ($group === 'gastos') {
            return match ($field) {
                // Si en tu UI "gastos_generales" significa "suma de tipo != liberacion"
                'gastos_generales' => $num($gastosGenerales->sum('monto')),
                'gastos_liberacion' => $num($gastosLiberacion->sum('monto')),
                'total_gastos'     => $num($gastosAll->sum('monto')),
                default            => '',
            };
        }

        // =========================
        // 2) Keys sin prefijo (compat)
        // =========================
        return match ($key) {
            // contenedores
            'numero_contenedor'   => (string) $c->numero_contenedor,
            'cliente'             => (string) $c->cliente,
            'fecha_arribo'        => $date($c->fecha_llegada),
            'fecha_llegada'       => $date($c->fecha_llegada),
            'proveedor'           => (string) $c->proveedor,
            'naviera'             => (string) $c->naviera,
            'mercancia_recibida'  => (string) $c->mercancia_recibida,
            'estado'              => (string) ($c->estado_label ?? $c->estado),
            'fecha_registro'      => $dt($c->created_at),
            'dias_transcurridos'  => $c->fecha_llegada ? Carbon::parse($c->fecha_llegada)->diffInDays(now()) : 0,

            // compat docs
            'docs_enviados' => is_null($docs?->enviado) ? '' : ($docs->enviado ? 'Sí' : 'No'),
            'fecha_envio'   => $date($docs?->fecha_envio),

            // compat cot
            'cotizacion_fecha_pago' => $date($cot?->fecha_pago),
            'impuestos'    => $num($cot?->impuestos),
            'honorarios'   => $num($cot?->honorarios),
            'maniobras'    => $num($cot?->maniobras),
            'almacenaje'   => $num($cot?->almacenaje),
            'total_cotizacion' => $num($cot?->total ?? null),

            // compat gastos
            'gastos_generales_total' => $num($gastosGenerales->sum('monto')),
            'gastos_liberacion_total'=> $num($gastosLiberacion->sum('monto')),
            'total_gastos'           => $num($gastosAll->sum('monto')),

            default => '',
        };
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1:1')->getFont()->setBold(true)->setSize(12);
        $sheet->getRowDimension(1)->setRowHeight(22);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $colCount = count($this->fields);
                $lastCol = Coordinate::stringFromColumnIndex(max(1, $colCount));
                $lastRow = $sheet->getHighestRow();

                $headerRange = "A1:{$lastCol}1";
                $dataRange   = "A1:{$lastCol}{$lastRow}";

                // Header
                $sheet->getStyle($headerRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '0B2A4A'],
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'bold' => true,
                    ],
                    'alignment' => [
                        'vertical' => 'center',
                    ],
                ]);

                // Bordes
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '233044'],
                        ],
                    ],
                ]);

                // Zebra
                for ($r = 2; $r <= $lastRow; $r++) {
                    $rgb = ($r % 2 === 0) ? '0F172A' : '0B1220';
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => $rgb],
                        ],
                        'font' => [
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ]);
                }

                // Freeze + filter
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($dataRange);

                // Wrap
                $sheet->getStyle($dataRange)->getAlignment()->setVertical('center');
                $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);

                // ✅ Formato moneda automático para columnas cuyo key sea de dinero
                $moneyKeys = ['impuestos','honorarios','maniobras','almacenaje','total','total_gastos','gastos_generales','gastos_liberacion','costo_liberacion','garantia','costos_demora','flete_maritimo'];
                foreach ($this->fields as $i => $key) {
                    $k = $key;
                    // soporta "cotizacion.total" etc.
                    if (str_contains($k, '.')) $k = explode('.', $k, 2)[1];

                    if (in_array($k, $moneyKeys, true)) {
                        $col = Coordinate::stringFromColumnIndex($i + 1);
                        $sheet->getStyle("{$col}2:{$col}{$lastRow}")
                            ->getNumberFormat()
                            ->setFormatCode('"$"#,##0.00_-');
                    }
                }
            }
        ];
    }
}
