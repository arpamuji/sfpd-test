<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Submission;
use App\Services\ApprovalWorkflowService;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService,
        private ApprovalWorkflowService $approvalService
    ) {}

    /**
     * Display list of submissions for the current user.
     *
     * @return Response
     */
    public function index()
    {
        $user = Auth::user();

        $mySubmissions = $this->submissionService->getMySubmissions($user);
        $pendingApprovals = $this->approvalService->getPendingApprovals($user);

        return Inertia::render('Submissions/Index', [
            'mySubmissions' => $mySubmissions,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }

    /**
     * Show form to create a new submission.
     *
     * @return Response
     */
    public function create()
    {
        return Inertia::render('Submissions/Create');
    }

    /**
     * Store a new submission with file uploads.
     * Automatically submits for approval after creation.
     */
    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        \Log::info('Submission store called');
        \Log::info('Files from request:', ['files' => $request->allFiles()]);
        \Log::info('Files count:', ['count' => count($request->file('files') ?? [])]);

        $validated = $request->validated();
        $files = $request->file('files') ?? $request->file('files.0') ?? [];

        // Handle both 'files' and 'files[]' naming
        if (! is_array($files)) {
            $files = [$files];
        }

        \Log::info('Processed files:', ['count' => count($files)]);

        $submission = $this->submissionService->createSubmission(
            $validated,
            Auth::user(),
            $files
        );

        // Auto-submit into approval workflow
        $this->submissionService->submitForApproval($submission);

        return redirect()->route('submissions.show', $submission)
            ->with('success', 'Submission created and submitted for approval.');
    }

    /**
     * Display the specified submission.
     *
     * @return Response
     */
    public function show(Submission $submission)
    {
        // Load relationships for proper serialization
        $submission->load(['files', 'approvalLogs.approver.role', 'rejectedBy']);

        // Append approval logs with serialized data
        $submission->append(['can_approve', 'can_reject', 'rejected_by_user']);
        $submission->setAttribute('approval_logs', $submission->approvalLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'approver_name' => $log->approver_name,
                'approver_role' => $log->approver_role,
                'action' => $log->action,
                'notes' => $log->notes,
                'created_at' => $log->created_at->toIso8601String(),
            ];
        })->toArray());

        return Inertia::render('Submissions/Show', [
            'submission' => $submission,
        ]);
    }
}
