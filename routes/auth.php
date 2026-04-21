<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('2fa/verify', [TwoFactorController::class, 'showVerification'])->name('2fa.verify');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify']);
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
