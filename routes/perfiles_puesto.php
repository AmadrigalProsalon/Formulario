<?php

use App\Http\Controllers\PerfilPuestoApiController;
use App\Http\Controllers\PerfilPuestoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/perfiles-puesto', [PerfilPuestoController::class, 'index'])->name('perfiles-puesto.index');
        Route::post('/perfiles-puesto', [PerfilPuestoController::class, 'store'])->name('perfiles-puesto.store');
        Route::put('/perfiles-puesto/{perfil}', [PerfilPuestoController::class, 'update'])->name('perfiles-puesto.update');
        Route::delete('/perfiles-puesto/{perfil}', [PerfilPuestoController::class, 'destroy'])->name('perfiles-puesto.destroy');
    });

Route::get('/api/perfiles-puesto/areas', [PerfilPuestoApiController::class, 'areas'])
    ->name('api.perfiles-puesto.areas');

Route::get('/api/perfiles-puesto/por-departamento', [PerfilPuestoApiController::class, 'porDepartamento'])
    ->name('api.perfiles-puesto.por-departamento');

Route::get('/api/perfiles-puesto/buscar', [PerfilPuestoApiController::class, 'buscar'])
    ->name('api.perfiles-puesto.buscar');

Route::get('/api/perfiles-puesto/{perfil}', [PerfilPuestoApiController::class, 'detalle'])
    ->name('api.perfiles-puesto.detalle');
