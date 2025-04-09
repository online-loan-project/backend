<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'Login'])->name('login');
Route::post('register', [AuthController::class, 'Register'])->name('register');
Route::get('send/code', [AuthController::class, 'sendVerify'])->middleware('auth:sanctum')->name('sendVerify');
Route::post('verify/code', [AuthController::class, 'verifyOTP'])->middleware('auth:sanctum')->name('verifyCode');
