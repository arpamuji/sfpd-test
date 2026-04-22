<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // 2FA setup (for users without 2FA enabled)
    Route::get('2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('2fa/enable', [TwoFactorController::class, 'enable2fa'])->name('2fa.enable');

    // 2FA verification - accessible to ALL authenticated users (middleware checks inside controller)
    Route::get('2fa/verify', [TwoFactorController::class, 'showVerification'])->name('2fa.verify');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify']);

    // Protected routes - require 2FA verified
    Route::middleware('2fa')->group(function () {
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    });
});
