<?php

use App\Http\Controllers\Approvals\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Submissions\SubmissionController;
use App\Models\Submission;
use Illuminate\Support\Facades\Route;

// Explicit route model binding for UUID
Route::bind('submission', function ($value) {
    return Submission::where('id', $value)->firstOrFail();
});

Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');

    Route::get('/approvals/pending', [ApprovalController::class, 'pending'])->name('approvals.pending');
    Route::post('/approvals/{submission}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{submission}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});
