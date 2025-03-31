<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
  Route::get('borrowers', [App\Http\Controllers\Admin\BorrowerController::class, 'index'])->middleware('auth:sanctum')->name('borrowers.index');
  Route::get('borrowers/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'show'])->middleware('auth:sanctum')->name('borrowers.show');
  Route::post('borrowers/status/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'borrowerStatus'])->middleware('auth:sanctum')->name('borrowers.status');

  //group request loan routes
    Route::prefix('request-loan')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RequestLoanController::class, 'index'])->name('request-loan.index');
        Route::get('/{id}', [App\Http\Controllers\Admin\RequestLoanController::class, 'show'])->name('request-loan.show');
    });

    //group credit score routes
    Route::prefix('credit-score')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CreditScoreController::class, 'index'])->name('credit-score.index');
        Route::post('/reset/{id}', [App\Http\Controllers\Admin\CreditScoreController::class, 'resetCreditScore'])->name('credit-score.reset');
        Route::post('/update/{id}', [App\Http\Controllers\Admin\CreditScoreController::class, 'updateCreditScore'])->name('credit-score.update');
    });
});
