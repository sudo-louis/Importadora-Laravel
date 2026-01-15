<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\RolesPermisosController;
use App\Http\Controllers\RoleController;


Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Vistas simples
    Route::view('/contenedores', 'contenedores.index')->name('contenedores.index');
    Route::view('/plantillas', 'plantillas.index')->name('plantillas.index');
    Route::view('/usuarios', 'usuarios.index')->name('usuarios.index');
    Route::view('/actividad/contenedores', 'actividad.contenedores')->name('actividad.contenedores');
    Route::view('/actividad/usuarios', 'actividad.usuarios')->name('actividad.usuarios');

    // Contenedores
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
    Route::get('/reportes/autocomplete/clientes', [ReportesController::class, 'autocompleteClientes'])->name('reportes.autocomplete.clientes');
    Route::get('/reportes/autocomplete/contenedores', [ReportesController::class, 'autocompleteContenedores'])->name('reportes.autocomplete.contenedores');

    // Usuarios
    Route::get('/usuarios/data', [UsuariosController::class, 'data'])->name('usuarios.data'); // para cargar cards vía fetch
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');

    // usuarios
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

    // roles
    Route::post('/roles', [UsuariosController::class, 'rolesStore'])->name('roles.store');
    Route::put('/roles/{role}', [UsuariosController::class, 'rolesUpdate'])->name('roles.update');
    Route::delete('/roles/{role}', [UsuariosController::class, 'rolesDestroy'])->name('roles.destroy');
});

require __DIR__ . '/auth.php';
