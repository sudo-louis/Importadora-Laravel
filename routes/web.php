<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContenedorController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::get('/contenedores', function () {
        return view('contenedores.index');
    })->name('contenedores.index');

    Route::get('/reportes', function () {
        return view('reportes.index');
    })->name('reportes.index');

    Route::get('/plantillas', function () {
        return view('plantillas.index');
    })->name('plantillas.index');

    Route::get('/usuarios', function () {
        return view('usuarios.index');
    })->name('usuarios.index');

    Route::get('/actividad/contenedores', function () {
        return view('actividad.contenedores');
    })->name('actividad.contenedores');

    Route::get('/actividad/usuarios', function () {
        return view('actividad.usuarios');
    })->name('actividad.usuarios');

    Route::resource('contenedores', ContenedorController::class)
    ->except(['create', 'edit'])
    ->parameters(['contenedores' => 'contenedor']);

    Route::put('contenedores/{contenedor}/liberacion', [ContenedorController::class, 'updateLiberacion'])
    ->name('contenedores.liberacion.update');
});

require __DIR__.'/auth.php';
