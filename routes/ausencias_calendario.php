<?php

use App\Http\Controllers\Admin\AusenciasCalendarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/ausencias/calendario', [AusenciasCalendarioController::class, 'index'])
        ->name('admin.ausencias.calendario');

    Route::get('/admin/permisos/calendario', function () {
        return redirect()->route('admin.ausencias.calendario');
    })->name('admin.permisos.calendario');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/permisos/calendario', function () {
        return redirect()->route('admin.ausencias.calendario');
    });
});
