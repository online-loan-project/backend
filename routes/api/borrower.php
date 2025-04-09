<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\BorrowerAccessMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('borrower')->middleware(['auth:sanctum', BorrowerAccessMiddleware::class])->group(function () {
    Route::get('me', [AuthController::class, 'me'])->name('borrower.me');

    Route::post('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'store'])->name('request-loan');
    Route::get('request-loan', [App\Http\Controllers\Borrower\RequestLoanController::class, 'show'])->name('request-loan');

    Route::prefix('nid-verify')->group(function () {
        Route::post('/', [App\Http\Controllers\Borrower\NidController::class, 'store'])->name('nid-verify');
        Route::get('/', [App\Http\Controllers\Borrower\NidController::class, 'show'])->name('nid-verify');
    });
});
