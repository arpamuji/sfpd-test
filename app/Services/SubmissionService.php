<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function __construct(
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    public function createSubmission(array $data, User $requestor): Submission
    {
        return DB::transaction(function () use ($data, $requestor) {
            $submission = $this->submissionRepository->create([
                'requestor_id' => $requestor->id,
                'warehouse_name' => $data['warehouse_name'],
                'warehouse_address' => $data['warehouse_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'budget_estimate' => $data['budget_estimate'],
                'description' => $data['description'] ?? null,
                'status' => 'draft',
            ]);

            return $submission;
        });
    }

    public function updateSubmission(Submission $submission, array $data): Submission
    {
        return $this->submissionRepository->update($submission, $data);
    }

    public function getSubmission(string $id): ?Submission
    {
        return $this->submissionRepository->find($id);
    }

    public function getMySubmissions(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->findByRequestor($user);
    }

    public function getMyDrafts(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->getDraftsByRequestor($user);
    }

    public function submitForApproval(Submission $submission): Submission
    {
        return $this->submissionRepository->update($submission, [
            'status' => 'pending_spv',
            'submitted_at' => now(),
        ]);
    }
}
