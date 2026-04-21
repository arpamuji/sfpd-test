<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    // 2FA setup (for users without 2FA enabled) - not protected by 2fa middleware
    Route::get('2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('2fa/enable', [TwoFactorController::class, 'enable2fa'])->name('2fa.enable');

    // 2FA verification (for users with 2FA enabled) - protected by 2fa middleware
    Route::middleware('2fa')->group(function () {
        Route::get('2fa/verify', [TwoFactorController::class, 'showVerification'])->name('2fa.verify');
        Route::post('2fa/verify', [TwoFactorController::class, 'verify']);
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    });
});
