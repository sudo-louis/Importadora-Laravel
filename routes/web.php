<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\UsuariosController;

// ✅ IMPORTANTE:
// Usa SOLO UNO de estos según cómo se llame tu clase final.
// Si tu clase se llama ActividadContenedoresController, deja este:
use App\Http\Controllers\ActividadContenedoresController;

// Si en tu proyecto lo dejaste como ActividadController (y ahí están los métodos),
// comenta el de arriba y usa este:
// use App\Http\Controllers\ActividadController;

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
    Route::get('/reportes/autocomplete/clientes', [ReportesController::class, 'autocompleteClientes'])->name('reportes.autocomplete.clientes');
    Route::get('/reportes/autocomplete/contenedores', [ReportesController::class, 'autocompleteContenedores'])->name('reportes.autocomplete.contenedores');

    // ================= USUARIOS (tabs) =================
    Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/data', [UsuariosController::class, 'data'])->name('usuarios.data');

    // Usuarios CRUD (store/update; destroy lo puedes bloquear en controller si quieres)
    Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');

    // Roles CRUD
    Route::post('/roles', [UsuariosController::class, 'rolesStore'])->name('roles.store');
    Route::put('/roles/{role}', [UsuariosController::class, 'rolesUpdate'])->name('roles.update');
    Route::delete('/roles/{role}', [UsuariosController::class, 'rolesDestroy'])->name('roles.destroy');

    // ================= ACTIVIDAD =================
    // Vistas
    Route::view('/actividad/contenedores', 'actividad.contenedores')->name('actividad.contenedores');
    Route::view('/actividad/usuarios', 'actividad.usuarios')->name('actividad.usuarios');

    // ✅ ENDPOINTS PARA AUTOCOMPLETE + SEARCH (Actividad por Contenedores)
    // OJO: esto requiere que exista SOLO UNA clase con ese nombre.
    Route::get('/actividad/contenedores/autocomplete', [ActividadContenedoresController::class, 'autocomplete'])
        ->name('actividad.contenedores.autocomplete');

    Route::get('/actividad/contenedores/search', [ActividadContenedoresController::class, 'search'])
        ->name('actividad.contenedores.search');
});

require __DIR__ . '/auth.php';
