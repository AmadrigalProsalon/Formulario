<?php

use App\Http\Controllers\Admin\PerfilPuestoController;
use App\Http\Controllers\Api\PerfilPuestoApiController;
use Illuminate\Support\Facades\Route;

Route::get('/api/perfiles-puesto/buscar', [PerfilPuestoApiController::class, 'buscar'])
    ->name('api.perfiles-puesto.buscar');

Route::get('/api/perfiles-puesto/{perfil}', [PerfilPuestoApiController::class, 'show'])
    ->name('api.perfiles-puesto.show');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/perfiles-puesto', [PerfilPuestoController::class, 'index'])->name('perfiles-puesto.index');
        Route::post('/perfiles-puesto/importar', [PerfilPuestoController::class, 'importar'])->name('perfiles-puesto.importar');
        Route::get('/perfiles-puesto/{perfil}', [PerfilPuestoController::class, 'show'])->name('perfiles-puesto.show');
        Route::put('/perfiles-puesto/{perfil}', [PerfilPuestoController::class, 'update'])->name('perfiles-puesto.update');
        Route::post('/perfiles-puesto/{perfil}/activar', [PerfilPuestoController::class, 'activar'])->name('perfiles-puesto.activar');
        Route::post('/perfiles-puesto/{perfil}/desactivar', [PerfilPuestoController::class, 'desactivar'])->name('perfiles-puesto.desactivar');
        Route::get('/perfiles-puesto/{perfil}/descargar-original', [PerfilPuestoController::class, 'descargarOriginal'])->name('perfiles-puesto.descargar-original');
    });
