<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'Login'])->name('login');
Route::post('register', [AuthController::class, 'Register'])->name('register');
Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');
