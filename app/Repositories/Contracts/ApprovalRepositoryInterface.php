<?php

namespace App\Repositories\Contracts;

use App\Models\Submission;
use App\Models\User;

interface ApprovalRepositoryInterface
{
    public function approve(Submission $submission, User $approver, ?string $notes): Submission;

    public function reject(Submission $submission, User $approver, string $notes): Submission;

    public function getApprovalHistory(Submission $submission);
}
