<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\AdminAccessMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth:sanctum', AdminAccessMiddleware::class])->group(function () {
  Route::get('me', [AuthController::class, 'me'])->name('admin.me');

  Route::get('borrowers', [App\Http\Controllers\Admin\BorrowerController::class, 'index'])->name('borrowers.index');
  Route::get('borrowers/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'show'])->name('borrowers.show');
  Route::post('borrowers/status/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'borrowerStatus'])->name('borrowers.status');

  //group request loan routes
    Route::prefix('request-loan')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RequestLoanController::class, 'index'])->name('request-loan.index');
        Route::get('/{id}', [App\Http\Controllers\Admin\RequestLoanController::class, 'show'])->name('request-loan.show');
        Route::get('/eligibility', [App\Http\Controllers\Admin\RequestLoanController::class, 'eligibilityList'])->name('request-loan.eligibility');
    });

    //group credit score routes
    Route::prefix('credit-score')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CreditScoreController::class, 'index'])->name('credit-score.index');
        Route::post('/reset/{id}', [App\Http\Controllers\Admin\CreditScoreController::class, 'resetCreditScore'])->name('credit-score.reset');
        Route::post('/update/{id}', [App\Http\Controllers\Admin\CreditScoreController::class, 'updateCreditScore'])->name('credit-score.update');
    });
});
