<?php

namespace App\Repositories;

use App\Models\ApprovalLog;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        // Log the approval
        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'approve',
            'notes' => $notes,
        ]);

        // Move to next role
        $currentRole = $submission->currentRole;
        $nextRole = $currentRole->nextRole;

        if ($nextRole === null) {
            // Final approval
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

    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        // Log the rejection
        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'reject',
            'notes' => $notes,
        ]);

        // Get first approver role (SPV Gudang)
        $firstApproverRole = Role::where('name', 'SPV Gudang')->first();

        // Reset to draft
        $submission->update([
            'status' => 'draft',
            'current_role_id' => $firstApproverRole->id,
            'rejected_by' => $approver->id,
            'rejection_reason' => $notes,
        ]);

        return $submission->fresh();
    }

    public function getApprovalHistory(Submission $submission)
    {
        return $submission->approvalLogs()
            ->with('approver.role')
            ->latest()
            ->get();
    }

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
