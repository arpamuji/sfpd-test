<?php

namespace App\Repositories;

use App\Models\ApprovalLog;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use Illuminate\Support\Collection;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    private const REQUESTOR_ROLE = 'Requestor';

    /**
     * Approve a submission and advance to next role or mark as approved.
     *
     * @param Submission $submission
     * @param User $approver
     * @param string|null $notes Optional approval notes
     * @return Submission The updated submission
     */
    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        // Cannot approve rejected or already approved submissions
        if ($submission->isRejected() || $submission->isApproved()) {
            throw new \InvalidArgumentException('Cannot approve a submission that is already rejected or approved.');
        }

        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'approve',
            'notes' => $notes,
        ]);

        $currentRole = $submission->currentRole;
        $nextRole = $currentRole->nextRole;

        if ($nextRole === null) {
            $submission->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        } else {
            $submission->update([
                'current_role_id' => $nextRole->id,
                'status' => $this->getStatusForRole($nextRole),
            ]);
        }

        return $submission->fresh();
    }

    /**
     * Reject a submission. Submission stays rejected - requestor must create new submission.
     *
     * @param Submission $submission
     * @param User $approver
     * @param string $notes Rejection reason (required)
     * @return Submission The updated submission (status: rejected)
     */
    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'reject',
            'notes' => $notes,
        ]);

        $submission->update([
            'status' => 'rejected',
            'rejected_by' => $approver->id,
            'rejection_reason' => $notes,
        ]);

        return $submission->fresh();
    }

    /**
     * Get approval history for a submission.
     *
     * @param Submission $submission
     * @return Collection<int, ApprovalLog>
     */
    public function getApprovalHistory(Submission $submission)
    {
        return $submission->approvalLogs()
            ->with('approver.role')
            ->latest()
            ->get();
    }

    /**
     * Get status string for a role.
     *
     * @param Role $role
     * @return string Status name
     */
    private function getStatusForRole(Role $role): string
    {
        return match ($role->name) {
            'SPV Gudang' => 'pending_spv',
            'Kepala Gudang' => 'pending_kepala',
            'Manager Operasional' => 'pending_manager_ops',
            'Direktur Operasional' => 'pending_direktur_ops',
            'Direktur Keuangan' => 'pending_direktur_keuangan',
            default => 'pending',
        };
    }
}
