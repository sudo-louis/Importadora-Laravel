<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;

// Actividad
use App\Http\Controllers\ActividadContenedoresController;
use App\Http\Controllers\ActividadUsuariosController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // ================= DASHBOARD =================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ================= CONTENEDORES =================
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

    // ================= PLANTILLAS =================
    Route::get('/plantillas', [PlantillaController::class, 'index'])->name('plantillas.index');
    Route::post('/plantillas', [PlantillaController::class, 'store'])->name('plantillas.store');
    Route::put('/plantillas/{plantilla}', [PlantillaController::class, 'update'])->name('plantillas.update');
    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy'])->name('plantillas.destroy');

    // ================= REPORTES =================
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/export', [ReportesController::class, 'export'])->name('reportes.export');
    Route::get('/reportes/autocomplete/clientes', [ReportesController::class, 'autocompleteClientes'])
        ->name('reportes.autocomplete.clientes');
    Route::get('/reportes/autocomplete/contenedores', [ReportesController::class, 'autocompleteContenedores'])
        ->name('reportes.autocomplete.contenedores');

    // ================= USUARIOS (módulo existente) =================
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/data', [UsuariosController::class, 'data'])->name('usuarios.data');

    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

    Route::post('/roles', [UsuariosController::class, 'rolesStore'])->name('roles.store');
    Route::put('/roles/{role}', [UsuariosController::class, 'rolesUpdate'])->name('roles.update');
    Route::delete('/roles/{role}', [UsuariosController::class, 'rolesDestroy'])->name('roles.destroy');

    // ================= ACTIVIDAD =================

    // Vistas
    Route::view('/actividad/contenedores', 'actividad.contenedores')->name('actividad.contenedores');
    Route::view('/actividad/usuarios', 'actividad.usuarios')->name('actividad.usuarios');

    // --- Actividad por Contenedores (OK) ---
    Route::get('/actividad/contenedores/autocomplete', [ActividadContenedoresController::class, 'autocomplete'])
        ->name('actividad.contenedores.autocomplete');

    Route::get('/actividad/contenedores/search', [ActividadContenedoresController::class, 'search'])
        ->name('actividad.contenedores.search');

    Route::get('/actividad/usuarios/list', [ActividadUsuariosController::class, 'list'])
        ->name('actividad.usuarios.list');

    Route::get('/actividad/usuarios/data', [ActividadUsuariosController::class, 'list'])
        ->name('actividad.usuarios.data');

    Route::get('/actividad/usuarios/{user}/logs', [ActividadUsuariosController::class, 'logs'])
        ->whereNumber('user')
        ->name('actividad.usuarios.logs');
});

require __DIR__ . '/auth.php';
