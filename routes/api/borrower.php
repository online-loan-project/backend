<?php

use Illuminate\Support\Facades\Route;

Route::prefix('borrower')->group(function () {
    Route::post('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'store'])->middleware('auth:sanctum')->name('request-loan');
    Route::get('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'show'])->middleware('auth:sanctum')->name('request-loan');
});
