<?php

namespace App\Http\Controllers;

use App\Services\ApprovalWorkflowService;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService,
        private ApprovalWorkflowService $approvalService
    ) {}

    /**
     * Display the dashboard with user's submissions and pending approvals.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();

        $mySubmissions = $this->submissionService->getMySubmissions($user);
        $myDrafts = $this->submissionService->getMyDrafts($user);
        $pendingApprovals = $this->approvalService->getPendingApprovals($user);

        // Calculate stats for the current user
        $pendingStatuses = ['pending_spv', 'pending_kepala', 'pending_manager', 'pending_direktur_ops', 'pending_direktur_keuangan'];
        $stats = [
            'total' => $mySubmissions->total(),
            'pending' => $mySubmissions->whereIn('status', $pendingStatuses)->count(),
            'approved' => $mySubmissions->where('status', 'approved')->count(),
            'rejected' => $mySubmissions->where('status', 'rejected')->count(),
        ];

        return Inertia::render('Dashboard/Dashboard', [
            'mySubmissions' => $mySubmissions,
            'myDrafts' => $myDrafts,
            'pendingApprovals' => $pendingApprovals,
            'stats' => $stats,
        ]);
    }
}
