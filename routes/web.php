<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ReportesController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Vistas simples
    Route::view('/usuarios', 'usuarios.index')->name('usuarios.index');
    Route::view('/actividad/contenedores', 'actividad.contenedores')->name('actividad.contenedores');
    Route::view('/actividad/usuarios', 'actividad.usuarios')->name('actividad.usuarios');

    // Contenedores (CRUD)
    Route::resource('contenedores', ContenedorController::class)
        ->except(['create', 'edit'])
        ->parameters(['contenedores' => 'contenedor']);

    Route::put('contenedores/{contenedor}/liberacion', [ContenedorController::class, 'updateLiberacion'])
        ->name('contenedores.liberacion.update');

    Route::put('contenedores/{contenedor}/envio-documentos', [ContenedorController::class, 'updateEnvioDocumentos'])
        ->name('contenedores.docs.update');

    Route::put('contenedores/{contenedor}/despacho', [ContenedorController::class, 'updateDespacho'])
        ->name('contenedores.despacho.update');

    Route::put('contenedores/{contenedor}/cotizacion', [ContenedorController::class, 'updateCotizacion'])
        ->name('contenedores.cotizacion.update');

    Route::put('contenedores/{contenedor}/gastos', [ContenedorController::class, 'updateGastos'])
        ->name('contenedores.gastos.update');

    // Plantillas (CRUD)
    Route::get('/plantillas', [PlantillaController::class, 'index'])->name('plantillas.index');
    Route::post('/plantillas', [PlantillaController::class, 'store'])->name('plantillas.store');
    Route::put('/plantillas/{plantilla}', [PlantillaController::class, 'update'])->name('plantillas.update');
    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy'])->name('plantillas.destroy');

    // Reportes
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/export', [ReportesController::class, 'export'])->name('reportes.export');

    // Autocomplete (AJAX)
    Route::get('/reportes/autocomplete/clientes', [ReportesController::class, 'clientes'])->name('reportes.autocomplete.clientes');
    Route::get('/reportes/autocomplete/contenedores', [ReportesController::class, 'contenedores'])->name('reportes.autocomplete.contenedores');
});

require __DIR__ . '/auth.php';
