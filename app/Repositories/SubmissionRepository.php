<?php

namespace App\Repositories;

use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubmissionRepository implements SubmissionRepositoryInterface
{
    private const PAGINATION_PER_PAGE = 15;

    /**
     * Create a new submission.
     *
     * @param array $data Submission data
     * @return Submission The created submission
     */
    public function create(array $data): Submission
    {
        return Submission::create($data);
    }

    /**
     * Update an existing submission.
     *
     * @param Submission $submission
     * @param array $data Data to update
     * @return Submission The refreshed submission
     */
    public function update(Submission $submission, array $data): Submission
    {
        $submission->update($data);
        return $submission->fresh();
    }

    /**
     * Find a submission by ID with relationships.
     *
     * @param string $id Submission UUID
     * @return Submission|null
     */
    public function find(string $id): ?Submission
    {
        return Submission::with(['files', 'approvalLogs.approver', 'currentRole', 'requestor'])
            ->find($id);
    }

    /**
     * Get submissions by requestor.
     *
     * @param User $user The requestor
     * @return LengthAwarePaginator<Submission>
     */
    public function findByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['currentRole', 'files'])
            ->where('requestor_id', $user->id)
            ->latest()
            ->paginate(self::PAGINATION_PER_PAGE);
    }

    /**
     * Get submissions pending at a specific role level.
     *
     * @param string $roleId Role UUID
     * @return LengthAwarePaginator<Submission>
     */
    public function findByCurrentRole(string $roleId): LengthAwarePaginator
    {
        return Submission::with(['requestor', 'files'])
            ->where('current_role_id', $roleId)
            ->whereNotIn('status', ['draft', 'approved', 'rejected'])
            ->latest()
            ->paginate(self::PAGINATION_PER_PAGE);
    }

    /**
     * Get draft submissions by requestor.
     *
     * @param User $user The requestor
     * @return LengthAwarePaginator<Submission>
     */
    public function getDraftsByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['files'])
            ->where('requestor_id', $user->id)
            ->where('status', 'draft')
            ->latest()
            ->paginate(self::PAGINATION_PER_PAGE);
    }
}
