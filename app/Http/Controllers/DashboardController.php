<?php

namespace App\Http\Controllers;

use App\Models\Contenedor;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // KPIs
        $contenedoresActivos = Contenedor::whereIn('estado', ['pendiente', 'en_proceso'])->count();

        // Garantías pendientes: liberación con devolucion_garantia = pendiente
        $garantiasPendientes = Contenedor::whereHas('liberacion', function ($q) {
            $q->where('devolucion_garantia', 'pendiente');
        })->count();

        // Revalidaciones: liberación con revalidacion = 1 y fecha_revalidacion null (pendiente)
        $revalidacionesPendientes = Contenedor::whereHas('liberacion', function ($q) {
            $q->where('revalidacion', 1)
              ->whereNull('fecha_revalidacion');
        })->count();

        // Sin envío de documentos: envioDocumento.enviado = 0 o no existe
        $sinEnvioDocs = Contenedor::where(function ($q) {
            $q->whereDoesntHave('envioDocumento')
              ->orWhereHas('envioDocumento', fn($d) => $d->where('enviado', false));
        })->count();

        // Contenedores recientes
        $recientes = Contenedor::latest()
            ->take(6)
            ->get();

        // Listas desplegables (como la imagen 2)
        $listaGarantias = Contenedor::with('liberacion')
            ->whereHas('liberacion', fn($q) => $q->where('devolucion_garantia', 'pendiente'))
            ->latest()
            ->take(10)
            ->get();

        $listaRevalidaciones = Contenedor::with('liberacion')
            ->whereHas('liberacion', fn($q) => $q->where('revalidacion', 1)->whereNull('fecha_revalidacion'))
            ->latest()
            ->take(10)
            ->get();

        $listaSinDocs = Contenedor::with('envioDocumento')
            ->where(function ($q) {
                $q->whereDoesntHave('envioDocumento')
                  ->orWhereHas('envioDocumento', fn($d) => $d->where('enviado', false));
            })
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.index', compact(
            'contenedoresActivos',
            'garantiasPendientes',
            'revalidacionesPendientes',
            'sinEnvioDocs',
            'recientes',
            'listaGarantias',
            'listaRevalidaciones',
            'listaSinDocs'
        ));
    }
}