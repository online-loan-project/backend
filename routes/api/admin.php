<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
  Route::get('borrowers', [App\Http\Controllers\Admin\BorrowerController::class, 'index'])->name('borrowers.index');
  Route::get('borrowers/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'show'])->name('borrowers.show');
  Route::post('borrowers/status/{id}', [App\Http\Controllers\Admin\BorrowerController::class, 'borrowerStatus'])->name('borrowers.status');

});
