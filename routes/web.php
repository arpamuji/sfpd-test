<?php

use App\Http\Controllers\Approvals\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Submissions\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Submissions
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/{id}', [SubmissionController::class, 'show'])->name('submissions.show');

    // Approvals
    Route::get('/approvals/pending', [ApprovalController::class, 'pending'])->name('approvals.pending');
    Route::post('/approvals/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});
