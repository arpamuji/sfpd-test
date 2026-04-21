<?php

namespace App\Repositories\Contracts;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubmissionRepositoryInterface
{
    public function create(array $data): Submission;

    public function update(Submission $submission, array $data): Submission;

    public function find(string $id): ?Submission;

    public function findByRequestor(User $user): LengthAwarePaginator;

    public function findByCurrentRole(string $roleId): LengthAwarePaginator;

    public function getDraftsByRequestor(User $user): LengthAwarePaginator;
}
