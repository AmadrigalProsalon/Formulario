<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExcelController;

/* FORM */
Route::get('/', [FormController::class,'index']);
Route::post('/guardar', [FormController::class,'store']);
Route::get('/data/{tipo}', [FormController::class,'getData']);

/* ADMIN */
Route::middleware(['auth'])->prefix('admin')->group(function(){

    Route::get('/', [AdminController::class,'dashboard']);

    // RESPUESTAS
    Route::get('/respuesta/{id}', [AdminController::class,'view']);

    // CAMPOS DINÁMICOS
    Route::get('/fields', [AdminController::class,'fields']);
    Route::post('/fields/store', [AdminController::class,'storeField']);
    Route::post('/fields/update/{id}', [AdminController::class,'updateField']);
    Route::delete('/fields/delete/{id}', [AdminController::class,'deleteField']);

    // IMPORT
    Route::get('/import', function () {
        return view('admin.import');
    });
    Route::post('/import-excel', [ExcelController::class, 'import']);

});

require __DIR__.'/auth.php';
