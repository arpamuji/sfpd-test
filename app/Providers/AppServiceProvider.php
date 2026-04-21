<?php

namespace App\Providers;

use App\Repositories\Contracts\SubmissionRepositoryInterface;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\SubmissionRepository;
use App\Repositories\ApprovalRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubmissionRepositoryInterface::class, SubmissionRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, ApprovalRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
