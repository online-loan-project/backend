<?php

use Illuminate\Support\Facades\Route;

Route::prefix('borrower')->group(function () {
    Route::post('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'store'])->middleware('auth:sanctum')->name('request-loan');
    Route::get('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'show'])->middleware('auth:sanctum')->name('request-loan');

    Route::prefix('nid-verify')->middleware('auth:sanctum')->group(function () {
        Route::post('/', [App\Http\Controllers\Borrower\NidController::class, 'store'])->name('nid-verify');
        Route::get('/', [App\Http\Controllers\Borrower\NidController::class, 'show'])->name('nid-verify');
    });
});
