<?php

use App\Http\Controllers\Admin\PermisosAdminController;
use App\Http\Controllers\Admin\PermisosEmpleadosController;
use App\Http\Controllers\Permisos\EmpleadoSearchController;
use App\Http\Controllers\Permisos\PermisoSolicitudController;
use Illuminate\Support\Facades\Route;

Route::prefix('permisos')->name('permisos.')->group(function () {
    Route::get('/solicitud', [PermisoSolicitudController::class, 'create'])->name('solicitud.create');
    Route::post('/solicitud', [PermisoSolicitudController::class, 'store'])->middleware('throttle:10,1')->name('solicitud.store');
    Route::get('/gracias', [PermisoSolicitudController::class, 'gracias'])->name('solicitud.gracias');
    Route::get('/empleados/buscar', EmpleadoSearchController::class)->name('empleados.buscar');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/permisos', [PermisosAdminController::class, 'index'])->name('permisos.index');
//    Route::get('/permisos/calendario', [PermisosAdminController::class, 'calendario'])->name('permisos.calendario');
    Route::get('/permisos/{permiso}', [PermisosAdminController::class, 'show'])->name('permisos.show');
    Route::post('/permisos/{permiso}/recibido', [PermisosAdminController::class, 'marcarRecibido'])->name('permisos.recibido');
    Route::post('/permisos/{permiso}/pendiente', [PermisosAdminController::class, 'marcarPendiente'])->name('permisos.pendiente');
    Route::post('/permisos/{permiso}/observaciones', [PermisosAdminController::class, 'marcarObservaciones'])->name('permisos.observaciones');
    Route::post('/permisos/{permiso}/cancelar', [PermisosAdminController::class, 'cancelar'])->name('permisos.cancelar');
    Route::get('/permisos/{permiso}/descargar', [PermisosAdminController::class, 'descargar'])->name('permisos.descargar');
    Route::post('/permisos/{permiso}/reenviar', [PermisosAdminController::class, 'reenviar'])->name('permisos.reenviar');
    Route::post('/permisos/{permiso}/formato-firmado', [PermisosAdminController::class, 'subirFormatoFirmado'])->name('permisos.formato_firmado.subir');
    Route::get('/permisos/{permiso}/formato-firmado', [PermisosAdminController::class, 'descargarFormatoFirmado'])->name('permisos.formato_firmado.descargar');

    Route::get('/permisos-catalogos/empleados', [PermisosEmpleadosController::class, 'index'])->name('permisos.empleados.index');
    Route::get('/permisos-catalogos/empleados/importar', [PermisosEmpleadosController::class, 'importForm'])->name('permisos.empleados.importar');
    Route::post('/permisos-catalogos/empleados/importar', [PermisosEmpleadosController::class, 'import'])->name('permisos.empleados.importar.store');
    Route::put('/permisos-catalogos/empleados/{empleado}', [PermisosEmpleadosController::class, 'update'])->name('permisos.empleados.update');
});
