<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormularioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FORMULARIOS PÚBLICOS
|--------------------------------------------------------------------------
*/

Route::get('/', [FormController::class, 'index'])->name('form.index');

Route::get('/f/{formulario:slug}', [FormController::class, 'index'])
    ->name('form.show');

Route::post('/guardar', [FormController::class, 'storeDefault'])
    ->middleware('throttle:6,1')
    ->name('form.store.default');

Route::post('/f/{formulario:slug}/guardar', [FormController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('form.store');

Route::get('/data/{tipo}', [FormController::class, 'getData'])
    ->where('tipo', '[A-Za-z0-9_\-]+')
    ->name('form.data');

Route::get('/gracias', function () {
    return view('gracias');
})->name('form.gracias');

/*
|--------------------------------------------------------------------------
| RUTA DASHBOARD PARA BREEZE
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('form.index');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN RH
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Usuarios
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [AdminUserController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{user}', [AdminUserController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('usuarios.destroy');

        // Formularios
        Route::get('/formularios', [FormularioController::class, 'index'])->name('formularios.index');
        Route::post('/formularios', [FormularioController::class, 'store'])->name('formularios.store');
        Route::put('/formularios/{formulario}', [FormularioController::class, 'update'])->name('formularios.update');
        Route::delete('/formularios/{formulario}', [FormularioController::class, 'destroy'])->name('formularios.destroy');
        Route::post('/formularios/{formulario}/toggle', [FormularioController::class, 'toggle'])->name('formularios.toggle');
        Route::post('/formularios/{formulario}/default', [FormularioController::class, 'makeDefault'])->name('formularios.default');

        // Respuestas
        Route::get('/respuestas', [AdminController::class, 'dashboard'])->name('respuestas.index');
        Route::get('/respuestas/export', [AdminController::class, 'exportRespuestas'])->name('respuestas.export');
        Route::get('/respuesta/{id}', [AdminController::class, 'view'])->name('respuesta.view');
        Route::post('/respuesta/update/{id}', [AdminController::class, 'update'])->name('respuesta.update');

        // Campos dinámicos
        Route::get('/fields', [AdminController::class, 'fields'])->name('fields.index');
        Route::post('/fields/store', [AdminController::class, 'storeField'])->name('fields.store');
        Route::get('/fields/edit/{id}', [AdminController::class, 'editField'])->name('fields.edit');
        Route::post('/fields/update/{id}', [AdminController::class, 'updateField'])->name('fields.update');
        Route::delete('/fields/delete/{id}', [AdminController::class, 'deleteField'])->name('fields.delete');
        Route::get('/fields/toggle/{id}', [AdminController::class, 'toggleField'])->name('fields.toggle');

        // Catálogos
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('catalogos.index');
        Route::post('/catalogos', [CatalogoController::class, 'store'])->name('catalogos.store');
        Route::put('/catalogos/{catalogo}', [CatalogoController::class, 'update'])->name('catalogos.update');
        Route::delete('/catalogos/{catalogo}', [CatalogoController::class, 'destroy'])->name('catalogos.destroy');

        // Importar Excel
        Route::get('/import', function () {
            return view('admin.import');
        })->name('import.view');

        Route::post('/import-excel', [ExcelController::class, 'import'])->name('import.excel');
    });
require __DIR__ . '/vacaciones.php';
require __DIR__ . '/auth.php';
