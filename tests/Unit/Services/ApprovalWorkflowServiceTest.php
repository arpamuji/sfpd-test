<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ApprovalWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApprovalRepositoryInterface $approvalRepository;
    private SubmissionRepositoryInterface $submissionRepository;
    private ApprovalWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->approvalRepository = Mockery::mock(ApprovalRepositoryInterface::class);
        $this->submissionRepository = Mockery::mock(SubmissionRepositoryInterface::class);
        $this->service = new ApprovalWorkflowService(
            $this->approvalRepository,
            $this->submissionRepository
        );
    }

    public function test_can_approve_returns_true_for_current_approver(): void
    {
        $approverRole = Role::create(['name' => 'SPV Gudang']);
        $approver = User::factory()->withRole($approverRole->id)->create();

        $submission = Submission::factory()->create(['current_role_id' => $approverRole->id]);

        $this->assertTrue($this->service->canApprove($submission, $approver));
    }

    public function test_can_approve_returns_false_for_wrong_role(): void
    {
        $wrongRole = Role::create(['name' => 'Kepala Gudang']);
        $user = User::factory()->withRole($wrongRole->id)->create();

        $spvRole = Role::create(['name' => 'SPV Gudang']);
        $submission = Submission::factory()->create(['current_role_id' => $spvRole->id]);

        $this->assertFalse($this->service->canApprove($submission, $user));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
