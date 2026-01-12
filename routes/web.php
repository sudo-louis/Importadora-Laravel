<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\PlantillaController;

/*
|--------------------------------------------------------------------------
| Redirect raíz
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Contenedores (módulo principal)
    |--------------------------------------------------------------------------
    */
    Route::resource('contenedores', ContenedorController::class)
        ->except(['create', 'edit'])
        ->parameters(['contenedores' => 'contenedor']);

    Route::put('contenedores/{contenedor}/liberacion', [ContenedorController::class, 'updateLiberacion'])
        ->name('contenedores.liberacion.update');

    Route::put('contenedores/{contenedor}/envio-documentos', [ContenedorController::class, 'updateEnvioDocumentos'])
        ->name('contenedores.docs.update');

    Route::put('contenedores/{contenedor}/cotizacion', [ContenedorController::class, 'updateCotizacion'])
        ->name('contenedores.cotizacion.update');

    Route::put('contenedores/{contenedor}/despacho', [ContenedorController::class, 'updateDespacho'])
        ->name('contenedores.despacho.update');

    Route::put('contenedores/{contenedor}/gastos', [ContenedorController::class, 'updateGastos'])
        ->name('contenedores.gastos.update');

    /*
    |--------------------------------------------------------------------------
    | Plantillas (CRUD completo)
    |--------------------------------------------------------------------------
    */
    Route::get('/plantillas', [PlantillaController::class, 'index'])
        ->name('plantillas.index');

    Route::post('/plantillas', [PlantillaController::class, 'store'])
        ->name('plantillas.store');

    Route::put('/plantillas/{plantilla}', [PlantillaController::class, 'update'])
        ->name('plantillas.update');

    Route::delete('/plantillas/{plantilla}', [PlantillaController::class, 'destroy'])
        ->name('plantillas.destroy');

    /*
    |--------------------------------------------------------------------------
    | Reportes
    |--------------------------------------------------------------------------
    */
    Route::view('/reportes', 'reportes.index')
        ->name('reportes.index');

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */
    Route::view('/usuarios', 'usuarios.index')
        ->name('usuarios.index');

    /*
    |--------------------------------------------------------------------------
    | Actividad
    |--------------------------------------------------------------------------
    */
    Route::view('/actividad/contenedores', 'actividad.contenedores')
        ->name('actividad.contenedores');

    Route::view('/actividad/usuarios', 'actividad.usuarios')
        ->name('actividad.usuarios');
});

require __DIR__.'/auth.php';