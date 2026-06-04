<?php

use App\Http\Controllers\AdminAreaController;
use App\Http\Controllers\AdminEmpleadoController;
use App\Http\Controllers\AdminPermisoController;
use App\Http\Controllers\AdminTipoPermisoController;
use App\Http\Controllers\PermisoPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/permisos/solicitud', [PermisoPublicController::class, 'create'])->name('permisos.solicitud');
Route::post('/permisos/solicitud', [PermisoPublicController::class, 'store'])->name('permisos.store');
Route::get('/permisos/gracias', [PermisoPublicController::class, 'gracias'])->name('permisos.gracias');

Route::get('/permisos/firmar/{token}', [PermisoPublicController::class, 'firma'])->name('permisos.firma.show');
Route::post('/permisos/firmar/{token}', [PermisoPublicController::class, 'firmar'])->name('permisos.firma.store');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/permisos', [AdminPermisoController::class, 'index'])->name('permisos.index');
        Route::get('/permisos/{solicitud}', [AdminPermisoController::class, 'show'])->name('permisos.show');
        Route::post('/permisos/{solicitud}/recibido', [AdminPermisoController::class, 'marcarRecibido'])->name('permisos.recibido');
        Route::post('/permisos/{solicitud}/pendiente', [AdminPermisoController::class, 'marcarPendiente'])->name('permisos.pendiente');
        Route::post('/permisos/{solicitud}/observaciones', [AdminPermisoController::class, 'observaciones'])->name('permisos.observaciones');
        Route::post('/permisos/{solicitud}/cancelar', [AdminPermisoController::class, 'cancelar'])->name('permisos.cancelar');

        Route::get('/permisos-catalogos/areas', [AdminAreaController::class, 'index'])->name('permisos.areas.index');
        Route::post('/permisos-catalogos/areas', [AdminAreaController::class, 'store'])->name('permisos.areas.store');
        Route::put('/permisos-catalogos/areas/{area}', [AdminAreaController::class, 'update'])->name('permisos.areas.update');
        Route::delete('/permisos-catalogos/areas/{area}', [AdminAreaController::class, 'destroy'])->name('permisos.areas.destroy');

        Route::get('/permisos-catalogos/empleados', [AdminEmpleadoController::class, 'index'])->name('permisos.empleados.index');
        Route::post('/permisos-catalogos/empleados', [AdminEmpleadoController::class, 'store'])->name('permisos.empleados.store');
        Route::put('/permisos-catalogos/empleados/{empleado}', [AdminEmpleadoController::class, 'update'])->name('permisos.empleados.update');
        Route::delete('/permisos-catalogos/empleados/{empleado}', [AdminEmpleadoController::class, 'destroy'])->name('permisos.empleados.destroy');

        Route::get('/permisos-catalogos/tipos', [AdminTipoPermisoController::class, 'index'])->name('permisos.tipos.index');
        Route::post('/permisos-catalogos/tipos', [AdminTipoPermisoController::class, 'store'])->name('permisos.tipos.store');
        Route::put('/permisos-catalogos/tipos/{tipoPermiso}', [AdminTipoPermisoController::class, 'update'])->name('permisos.tipos.update');
        Route::delete('/permisos-catalogos/tipos/{tipoPermiso}', [AdminTipoPermisoController::class, 'destroy'])->name('permisos.tipos.destroy');
    });
