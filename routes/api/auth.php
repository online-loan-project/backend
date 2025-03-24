<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'Login'])->name('api.login');
Route::post('register', [AuthController::class, 'Register'])->name('api.register');
