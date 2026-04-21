<?php

namespace App\Http\Controllers\Approvals;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveSubmissionRequest;
use App\Models\Submission;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalWorkflowService $approvalService) {}

    /**
     * Display pending approvals for the current user.
     *
     * @return \Inertia\Response
     */
    public function pending()
    {
        $user = Auth::user();
        $submissions = $this->approvalService->getPendingApprovals($user);

        return Inertia::render('Dashboard/Dashboard', [
            'pendingSubmissions' => $submissions,
        ]);
    }

    /**
     * Approve a submission.
     *
     * @param ApproveSubmissionRequest $request
     * @param Submission $submission
     * @return RedirectResponse
     */
    public function approve(ApproveSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->approvalService->canApprove($submission, $user)) {
            abort(403, 'You cannot approve this submission at this level.');
        }

        try {
            $this->approvalService->approveSubmission(
                $submission,
                $user,
                $request->input('notes')
            );

            return redirect()->route('submissions.show', $submission)->with('success', 'Submission approved.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reject a submission.
     *
     * @param ApproveSubmissionRequest $request
     * @param Submission $submission
     * @return RedirectResponse
     */
    public function reject(ApproveSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->approvalService->canApprove($submission, $user)) {
            abort(403, 'You cannot reject this submission at this level.');
        }

        try {
            $this->approvalService->rejectSubmission(
                $submission,
                $user,
                $request->input('notes')
            );

            return redirect()->route('submissions.show', $submission)->with('success', 'Submission rejected.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
