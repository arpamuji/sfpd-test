<?php

namespace App\Http\Controllers\Approvals;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveSubmissionRequest;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprovalController extends Controller
{
    public function __construct(private ApprovalWorkflowService $approvalService) {}

    public function pending()
    {
        $user = Auth::user();
        $submissions = $this->approvalService->getPendingApprovals($user);

        return Inertia::render('Dashboard/Dashboard', [
            'pendingSubmissions' => $submissions,
        ]);
    }

    public function approve(ApproveSubmissionRequest $request, string $id): RedirectResponse
    {
        $submission = $this->approvalService->getSubmission($id);

        if (!$submission) {
            abort(404);
        }

        try {
            $this->approvalService->approveSubmission(
                $submission,
                Auth::user(),
                $request->input('notes')
            );

            return redirect()->route('submissions.show', $submission)->with('success', 'Submission approved.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(ApproveSubmissionRequest $request, string $id): RedirectResponse
    {
        $submission = $this->approvalService->getSubmission($id);

        if (!$submission) {
            abort(404);
        }

        try {
            $this->approvalService->rejectSubmission(
                $submission,
                Auth::user(),
                $request->input('notes')
            );

            return redirect()->route('submissions.show', $submission)->with('success', 'Submission rejected.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
