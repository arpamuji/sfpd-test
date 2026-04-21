<?php

namespace App\Repositories;

use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubmissionRepository implements SubmissionRepositoryInterface
{
    public function create(array $data): Submission
    {
        return Submission::create($data);
    }

    public function update(Submission $submission, array $data): Submission
    {
        $submission->update($data);
        return $submission->fresh();
    }

    public function find(string $id): ?Submission
    {
        return Submission::with(['files', 'approvalLogs.approver', 'currentRole', 'requestor'])
            ->find($id);
    }

    public function findByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['currentRole', 'files'])
            ->where('requestor_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function findByCurrentRole(string $roleId): LengthAwarePaginator
    {
        return Submission::with(['requestor', 'files'])
            ->where('current_role_id', $roleId)
            ->whereNotIn('status', ['draft', 'approved', 'rejected'])
            ->latest()
            ->paginate(15);
    }

    public function getDraftsByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['files'])
            ->where('requestor_id', $user->id)
            ->where('status', 'draft')
            ->latest()
            ->paginate(15);
    }
}
