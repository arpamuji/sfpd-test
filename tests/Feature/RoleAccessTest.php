<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use App\Repositories\ApprovalRepository;
use App\Repositories\SubmissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_own_submissions(): void
    {
        $requestorRole = Role::create(['name' => 'Requestor']);
        $user1 = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);
        $user2 = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $currentRole = Role::create(['name' => 'SPV Gudang']);
        Submission::factory()->count(3)->create([
            'requestor_id' => $user1->id,
            'current_role_id' => $currentRole->id,
        ]);
        Submission::factory()->count(2)->create([
            'requestor_id' => $user2->id,
            'current_role_id' => $currentRole->id,
        ]);

        $repo = new SubmissionRepository();
        $result = $repo->findByRequestor($user1);

        $this->assertEquals(3, $result->total());
    }

    public function test_approver_sees_only_pending_for_their_role(): void
    {
        $spvRole = Role::create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::create(['name' => 'Kepala Gudang']);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);

        $approvalRepo = new ApprovalRepository();
        $submissionRepo = new SubmissionRepository();
        $service = new ApprovalWorkflowService($approvalRepo, $submissionRepo);

        // Create submission with explicit current_role_id
        Submission::create([
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
            'requestor_id' => $spv->id,
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
        ]);

        Submission::create([
            'current_role_id' => $kepalaRole->id,
            'status' => 'pending_kepala',
            'requestor_id' => $spv->id,
            'warehouse_name' => 'Test Warehouse 2',
            'warehouse_address' => '456 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
        ]);

        $pending = $service->getPendingApprovals($spv);

        $this->assertCount(1, $pending);
        $this->assertEquals($spvRole->id, $pending->first()->current_role_id);
    }
}
