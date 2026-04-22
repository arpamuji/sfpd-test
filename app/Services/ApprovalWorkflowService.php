<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Support\Collection;

class ApprovalWorkflowService
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    /**
     * Approve a submission.
     *
     * @param Submission $submission The submission to approve
     * @param User $approver The user approving
     * @param string|null $notes Optional approval notes
     * @return Submission The updated submission
     */
    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->approvalRepository->approve($submission, $approver, $notes);
    }

    /**
     * Reject a submission.
     *
     * @param Submission $submission The submission to reject
     * @param User $approver The user rejecting
     * @param string $notes Rejection reason (required)
     * @return Submission The updated submission
     */
    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        return $this->approvalRepository->reject($submission, $approver, $notes);
    }

    /**
     * Approve a submission (alias for approve()).
     *
     * @param Submission $submission
     * @param User $approver
     * @param string|null $notes
     * @return Submission
     */
    public function approveSubmission(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->approve($submission, $approver, $notes);
    }

    /**
     * Reject a submission (alias for reject()).
     *
     * @param Submission $submission
     * @param User $approver
     * @param string|null $notes
     * @return Submission
     */
    public function rejectSubmission(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->reject($submission, $approver, $notes ?? 'Rejected');
    }

    /**
     * Get submissions pending approval for a user.
     *
     * @param User $user The approver
     * @return Collection<int, Submission>
     */
    public function getPendingApprovals(User $user): Collection
    {
        return $this->getPendingForRole($user->role_id);
    }

    /**
     * Get a submission by ID.
     *
     * @param string $id Submission UUID
     * @return Submission|null
     */
    public function getSubmission(string $id): ?Submission
    {
        return $this->submissionRepository->find($id);
    }

    /**
     * Get submissions pending at a specific role level.
     *
     * @param string $roleId Role UUID
     * @return Collection<int, Submission>
     */
    public function getPendingForRole(string $roleId): Collection
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

    /**
     * Check if a user can approve a submission.
     *
     * @param Submission $submission
     * @param User $user
     * @return bool True if user's role matches submission's current role
     */
    public function canApprove(Submission $submission, User $user): bool
    {
        return $submission->current_role_id === $user->role_id;
    }
}
