<?php

use App\Http\Controllers\Api\PerfilPuestoAutofillController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api/perfiles-puesto')->group(function () {
    Route::get('/departamentos', [PerfilPuestoAutofillController::class, 'departamentos'])
        ->name('api.perfiles-puesto.departamentos');

    Route::get('/', [PerfilPuestoAutofillController::class, 'perfiles'])
        ->name('api.perfiles-puesto.index');

    Route::get('/{id}', [PerfilPuestoAutofillController::class, 'show'])
        ->whereNumber('id')
        ->name('api.perfiles-puesto.show');
});
