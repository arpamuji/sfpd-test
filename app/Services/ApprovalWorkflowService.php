<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\SubmissionRepositoryInterface;

class ApprovalWorkflowService
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->approvalRepository->approve($submission, $approver, $notes);
    }

    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        return $this->approvalRepository->reject($submission, $approver, $notes);
    }

    public function approveSubmission(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->approve($submission, $approver, $notes);
    }

    public function rejectSubmission(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->reject($submission, $approver, $notes ?? 'Rejected');
    }

    public function getPendingApprovals(User $user): \Illuminate\Support\Collection
    {
        return $this->getPendingForRole($user->role_id);
    }

    public function getSubmission(string $id): ?Submission
    {
        return $this->submissionRepository->find($id);
    }

    public function getPendingForRole(string $roleId): \Illuminate\Support\Collection
    {
        $role = Role::find($roleId);
        if (!$role) {
            return collect([]);
        }

        return $role->submissionsAtThisLevel()
            ->whereNotIn('status', ['draft', 'approved', 'rejected'])
            ->latest()
            ->get();
    }

    public function canApprove(Submission $submission, User $user): bool
    {
        return $submission->current_role_id === $user->role_id;
    }
}
