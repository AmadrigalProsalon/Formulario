<?php

use App\Http\Controllers\AdminVacacionesController;
use App\Http\Controllers\VacacionesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vacaciones - público
|--------------------------------------------------------------------------
*/

Route::get('/vacaciones/solicitud', [VacacionesController::class, 'create'])
    ->name('vacaciones.create');

Route::post('/vacaciones/consultar-empleado', [VacacionesController::class, 'consultarEmpleado'])
    ->name('vacaciones.consultar-empleado');

Route::post('/vacaciones/calcular-dias', [VacacionesController::class, 'calcularDias'])
    ->name('vacaciones.calcular-dias');

Route::post('/vacaciones/solicitud', [VacacionesController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('vacaciones.store');

/*
|--------------------------------------------------------------------------
| Vacaciones - admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin/vacaciones')
    ->name('admin.vacaciones.')
    ->group(function () {
        Route::get('/', [AdminVacacionesController::class, 'index'])->name('index');
        Route::post('/solicitudes/{solicitud}/aprobar', [AdminVacacionesController::class, 'aprobar'])->name('solicitudes.aprobar');
        Route::post('/solicitudes/{solicitud}/rechazar', [AdminVacacionesController::class, 'rechazar'])->name('solicitudes.rechazar');

        Route::get('/empleados', [AdminVacacionesController::class, 'empleados'])->name('empleados.index');
        Route::post('/empleados', [AdminVacacionesController::class, 'storeEmpleado'])->name('empleados.store');
        Route::put('/empleados/{empleado}', [AdminVacacionesController::class, 'updateEmpleado'])->name('empleados.update');
        Route::post('/empleados/{empleado}/ajustes', [AdminVacacionesController::class, 'storeAjuste'])->name('empleados.ajustes.store');

        Route::get('/dias-inhabiles', [AdminVacacionesController::class, 'diasInhabiles'])->name('inhabiles.index');
        Route::post('/dias-inhabiles', [AdminVacacionesController::class, 'storeDiaInhabil'])->name('inhabiles.store');
        Route::delete('/dias-inhabiles/{dia}', [AdminVacacionesController::class, 'destroyDiaInhabil'])->name('inhabiles.destroy');
    });
