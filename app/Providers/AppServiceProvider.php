<?php

namespace App\Providers;

use App\Repositories\ApprovalRepository;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use App\Repositories\SubmissionRepository;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubmissionRepositoryInterface::class, SubmissionRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, ApprovalRepository::class);
    }

    public function boot(): void
    {
        Inertia::handleExceptionsUsing(function ($exceptionResponse) {
            // In production, render specific status codes as Inertia pages
            if (! app()->environment(['local', 'testing'])) {
                if (in_array($exceptionResponse->statusCode(), [403, 404, 429, 500, 503])) {
                    return $exceptionResponse->render('ErrorPage', [
                        'status' => $exceptionResponse->statusCode(),
                    ]);
                }
            }

            // Return null to let Laravel handle other cases
            return null;
        });
    }
}
