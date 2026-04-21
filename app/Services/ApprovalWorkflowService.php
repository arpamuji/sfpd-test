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

    public function getPendingForRole(string $roleId): array
    {
        $role = Role::find($roleId);
        if (!$role) {
            return [];
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
