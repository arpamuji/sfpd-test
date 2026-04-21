<?php

namespace App\Http\Controllers;

use App\Services\SubmissionService;
use App\Services\ApprovalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService,
        private ApprovalWorkflowService $approvalService
    ) {}

    public function index()
    {
        $user = Auth::user();

        $mySubmissions = $this->submissionService->getMySubmissions($user);
        $myDrafts = $this->submissionService->getMyDrafts($user);
        $pendingApprovals = $this->approvalService->getPendingApprovals($user);

        return Inertia::render('Dashboard/Dashboard', [
            'mySubmissions' => $mySubmissions,
            'myDrafts' => $myDrafts,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }
}
