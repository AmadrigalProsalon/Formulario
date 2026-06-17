<?php

use App\Http\Controllers\Permisos\CalendarioAusenciasController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/permisos/calendario', [CalendarioAusenciasController::class, 'index'])
            ->name('permisos.calendario');
    });
