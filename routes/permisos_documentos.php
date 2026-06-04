<?php

use App\Http\Controllers\Permisos\PermisoDocumentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])
    ->prefix('admin/permisos/documentos')
    ->name('admin.permisos.documentos.')
    ->group(function () {
        Route::get('/{solicitud}/inicial', [PermisoDocumentoController::class, 'descargarInicial'])->name('inicial');
        Route::get('/{solicitud}/firmado', [PermisoDocumentoController::class, 'descargarFirmado'])->name('firmado');
        Route::post('/{solicitud}/reenviar-inicial', [PermisoDocumentoController::class, 'reenviarInicial'])->name('reenviar_inicial');
        Route::post('/{solicitud}/reenviar-firmado-rh', [PermisoDocumentoController::class, 'reenviarFirmadoRh'])->name('reenviar_firmado_rh');
    });
