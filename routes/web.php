<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    // Dashboard: lo dejamos libre solo con auth
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================
    // CONTENEDORES
    // =========================

    // Vista principal contenedores (registro)
    Route::view('/contenedores', 'contenedores.index')
        ->middleware('module:registro')
        ->name('contenedores.index');

    Route::resource('contenedores', ContenedorController::class)
        ->middleware('module:registro')
        ->except(['create', 'edit'])
        ->parameters(['contenedores' => 'contenedor']);

    Route::put('contenedores/{contenedor}/liberacion', [ContenedorController::class, 'updateLiberacion'])
        ->middleware('module:liberacion')
        ->name('contenedores.liberacion.update');

    Route::put('contenedores/{contenedor}/envio-documentos', [ContenedorController::class, 'updateEnvioDocumentos'])
        ->middleware('module:docs')
        ->name('contenedores.docs.update');

    Route::put('contenedores/{contenedor}/despacho', [ContenedorController::class, 'updateDespacho'])
        ->middleware('module:despacho')
        ->name('contenedores.despacho.update');

    Route::put('contenedores/{contenedor}/cotizacion', [ContenedorController::class, 'updateCotizacion'])
        ->middleware('module:cotizacion')
        ->name('contenedores.cotizacion.update');

    Route::put('contenedores/{contenedor}/gastos', [ContenedorController::class, 'updateGastos'])
        ->middleware('module:gastos')
        ->name('contenedores.gastos.update');

    // =========================
    // PLANTILLAS (de momento solo auth)
    // Si luego quieres controlarlas por permisos:
    // crea permisos modulo=plantillas tipo=ver/crear/editar/eliminar
    // y agrega ->middleware('module:plantillas')
    // =========================
    Route::get('/plantillas', [PlantillaController::class, 'index'])->name('plantillas.index');
    Route::post('/plantillas', [PlantillaController::class, 'store'])->name('plantillas.store');
    Route::put('/plantillas/{plantilla}', [PlantillaController::class, 'update'])->name('plantillas.update');
    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy'])->name('plantillas.destroy');

    // =========================
    // REPORTES
    // =========================
    Route::get('/reportes', [ReportesController::class, 'index'])
        ->middleware('module:reportes')
        ->name('reportes.index');

    Route::get('/reportes/export', [ReportesController::class, 'export'])
        ->middleware('module:reportes')
        ->name('reportes.export');

    Route::get('/reportes/autocomplete/clientes', [ReportesController::class, 'autocompleteClientes'])
        ->middleware('module:reportes')
        ->name('reportes.autocomplete.clientes');

    Route::get('/reportes/autocomplete/contenedores', [ReportesController::class, 'autocompleteContenedores'])
        ->middleware('module:reportes')
        ->name('reportes.autocomplete.contenedores');

    // =========================
    // ACTIVIDAD
    // =========================
    Route::view('/actividad/contenedores', 'actividad.contenedores')
        ->middleware('module:actividad')
        ->name('actividad.contenedores');

    Route::view('/actividad/usuarios', 'actividad.usuarios')
        ->middleware('module:actividad')
        ->name('actividad.usuarios');

    // =========================
    // USUARIOS + ROLES
    // =========================
    Route::get('/usuarios', [UsuariosController::class, 'index'])
        ->middleware('module:usuarios')
        ->name('usuarios.index');

    Route::get('/usuarios/data', [UsuariosController::class, 'data'])
        ->middleware('module:usuarios')
        ->name('usuarios.data');

    Route::post('/usuarios', [UsuariosController::class, 'store'])
        ->middleware('module:usuarios')
        ->name('usuarios.store');

    Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])
        ->middleware('module:usuarios')
        ->name('usuarios.update');

    Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])
        ->middleware('module:usuarios')
        ->name('usuarios.destroy');

    Route::post('/roles', [UsuariosController::class, 'rolesStore'])
        ->middleware('module:usuarios')
        ->name('roles.store');

    Route::put('/roles/{role}', [UsuariosController::class, 'rolesUpdate'])
        ->middleware('module:usuarios')
        ->name('roles.update');

    Route::delete('/roles/{role}', [UsuariosController::class, 'rolesDestroy'])
        ->middleware('module:usuarios')
        ->name('roles.destroy');
});

require __DIR__ . '/auth.php';
