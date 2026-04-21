<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    private const REQUESTOR_ROLE = 'Requestor';

    public function __construct(
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    /**
     * Create a new submission with optional file uploads.
     *
     * @param array{warehouse_name: string, warehouse_address: string, latitude: float, longitude: float, budget_estimate: float, description?: string} $data
     * @param User $requestor The user creating the submission
     * @param array|null $files Uploaded files (3-10 files, PDF/JPG/PNG only, 5MB max each)
     * @return Submission The created submission in draft status
     */
    public function createSubmission(array $data, User $requestor, ?array $files = []): Submission
    {
        return DB::transaction(function () use ($data, $requestor, $files) {
            $submission = $this->submissionRepository->create([
                'requestor_id' => $requestor->id,
                'current_role_id' => $requestor->role_id,
                'warehouse_name' => $data['warehouse_name'],
                'warehouse_address' => $data['warehouse_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'budget_estimate' => $data['budget_estimate'],
                'description' => $data['description'] ?? null,
                'status' => 'draft',
            ]);

            if (!empty($files)) {
                foreach ($files as $file) {
                    $path = $file->store('submissions/' . $submission->id, 'public');
                    SubmissionFile::create([
                        'submission_id' => $submission->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            return $submission;
        });
    }

    /**
     * Update an existing submission.
     *
     * @param Submission $submission The submission to update
     * @param array $data Data to update
     * @return Submission The refreshed submission
     */
    public function updateSubmission(Submission $submission, array $data): Submission
    {
        return $this->submissionRepository->update($submission, $data);
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
     * Get all submissions by a requestor.
     *
     * @param User $user The requestor
     * @return LengthAwarePaginator<Submission>
     */
    public function getMySubmissions(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->findByRequestor($user);
    }

    /**
     * Get draft submissions by a requestor.
     *
     * @param User $user The requestor
     * @return LengthAwarePaginator<Submission>
     */
    public function getMyDrafts(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->getDraftsByRequestor($user);
    }

    /**
     * Submit a draft submission for approval.
     * Changes status from draft to pending_spv and advances to SPV Gudang role.
     *
     * @param Submission $submission The submission to submit
     * @return Submission The updated submission
     */
    public function submitForApproval(Submission $submission): Submission
    {
        $requestorRole = Role::where('name', self::REQUESTOR_ROLE)->first();
        $firstApproverRole = Role::find($requestorRole->next_role_id);

        return $this->submissionRepository->update($submission, [
            'status' => 'pending_spv',
            'current_role_id' => $firstApproverRole->id,
            'submitted_at' => now(),
        ]);
    }
}
