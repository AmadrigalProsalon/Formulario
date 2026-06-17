<?php

use App\Http\Controllers\PerfilPuestoCsvController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/perfiles-puesto-csv', [PerfilPuestoCsvController::class, 'index'])->name('perfiles-puesto.csv');
        Route::post('/perfiles-puesto-csv/importar', [PerfilPuestoCsvController::class, 'importar'])->name('perfiles-puesto.csv.importar');
        Route::get('/perfiles-puesto-csv/plantilla', [PerfilPuestoCsvController::class, 'descargarPlantilla'])->name('perfiles-puesto.csv.plantilla');
    });

Route::get('/api/perfiles-puesto/departamentos', [PerfilPuestoCsvController::class, 'departamentosApi'])
    ->name('api.perfiles-puesto.departamentos');

Route::get('/api/perfiles-puesto/por-departamento', [PerfilPuestoCsvController::class, 'porDepartamento'])
    ->name('api.perfiles-puesto.por-departamento');

Route::get('/api/perfiles-puesto/{perfil}', [PerfilPuestoCsvController::class, 'showApi'])
    ->whereNumber('perfil')
    ->name('api.perfiles-puesto.show');
