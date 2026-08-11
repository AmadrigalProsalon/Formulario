<?php

use App\Http\Controllers\Admin\PermisosAdminController;
use App\Http\Controllers\Admin\CalendarioLaboralAdminController;
use App\Http\Controllers\Admin\PermisosEmpleadosController;
use App\Http\Controllers\Permisos\EmpleadoSearchController;
use App\Http\Controllers\Permisos\PermisoSolicitudController;
use Illuminate\Support\Facades\Route;

Route::prefix('permisos')->name('permisos.')->group(function () {
    Route::get('/solicitud', [PermisoSolicitudController::class, 'create'])->name('solicitud.create');
    Route::post('/solicitud', [PermisoSolicitudController::class, 'store'])->middleware('throttle:10,1')->name('solicitud.store');
    Route::get('/gracias', [PermisoSolicitudController::class, 'gracias'])->name('solicitud.gracias');
    Route::get('/empleados/buscar', EmpleadoSearchController::class)->name('empleados.buscar');
    Route::post('/fechas/validar', [PermisoSolicitudController::class, 'validarFechas'])->middleware('throttle:60,1')->name('fechas.validar');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/permisos', [PermisosAdminController::class, 'index'])->name('permisos.index');
//    Route::get('/permisos/calendario', [PermisosAdminController::class, 'calendario'])->name('permisos.calendario');
    Route::get('/permisos/{permiso}', [PermisosAdminController::class, 'show'])->name('permisos.show');
    Route::post('/permisos/{permiso}/recibido', [PermisosAdminController::class, 'marcarRecibido'])->name('permisos.recibido');
    Route::post('/permisos/{permiso}/pendiente', [PermisosAdminController::class, 'marcarPendiente'])->name('permisos.pendiente');
    Route::post('/permisos/{permiso}/observaciones', [PermisosAdminController::class, 'marcarObservaciones'])->name('permisos.observaciones');
    Route::post('/permisos/{permiso}/rechazar', [PermisosAdminController::class, 'rechazar'])->name('permisos.rechazar');
    Route::post('/permisos/{permiso}/cancelar', [PermisosAdminController::class, 'cancelar'])->name('permisos.cancelar');
    Route::get('/permisos/{permiso}/descargar', [PermisosAdminController::class, 'descargar'])->name('permisos.descargar');
    Route::post('/permisos/{permiso}/reenviar', [PermisosAdminController::class, 'reenviar'])->name('permisos.reenviar');
    Route::post('/permisos/{permiso}/formato-firmado', [PermisosAdminController::class, 'subirFormatoFirmado'])->name('permisos.formato_firmado.subir');
    Route::delete('/permisos/{permiso}', [PermisosAdminController::class, 'destroy'])->name('permisos.destroy');
    Route::get('/permisos/{permiso}/formato-firmado', [PermisosAdminController::class, 'descargarFormatoFirmado'])->name('permisos.formato_firmado.descargar');

    Route::get('/permisos-catalogos/empleados', [PermisosEmpleadosController::class, 'index'])->name('permisos.empleados.index');
    Route::get('/permisos-catalogos/empleados/importar', [PermisosEmpleadosController::class, 'importForm'])->name('permisos.empleados.importar');
    Route::get('/permisos-catalogos/empleados/exportar', [PermisosEmpleadosController::class, 'export'])->name('permisos.empleados.exportar');
    Route::post('/permisos-catalogos/empleados/importar', [PermisosEmpleadosController::class, 'import'])->name('permisos.empleados.importar.store');
    Route::post('/permisos-catalogos/empleados', [PermisosEmpleadosController::class, 'store'])->name('permisos.empleados.store');
    Route::put('/permisos-catalogos/empleados/{empleado}', [PermisosEmpleadosController::class, 'update'])->name('permisos.empleados.update');
    Route::delete('/permisos-catalogos/empleados/{empleado}', [PermisosEmpleadosController::class, 'destroy'])->name('permisos.empleados.destroy');

    Route::get('/permisos-catalogos/calendario-laboral', [CalendarioLaboralAdminController::class, 'index'])->name('permisos.calendario-laboral.index');
    Route::put('/permisos-catalogos/calendario-laboral/areas/{area}', [CalendarioLaboralAdminController::class, 'updateArea'])->name('permisos.calendario-laboral.areas.update');
    Route::post('/permisos-catalogos/calendario-laboral/inhabiles', [CalendarioLaboralAdminController::class, 'storeInhabil'])->name('permisos.calendario-laboral.inhabiles.store');
    Route::delete('/permisos-catalogos/calendario-laboral/inhabiles/{dia}', [CalendarioLaboralAdminController::class, 'destroyInhabil'])->name('permisos.calendario-laboral.inhabiles.destroy');
});
