<?php

namespace Tests\Feature\Submissions;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_spv_can_approve_submission(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);

        // Set up the role chain: SPV -> Kepala
        $spvRole->update(['next_role_id' => $kepalaRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $response = $this->actingAs($spv)->post(route('approvals.approve', $submission), [
            'action' => 'approve',
        ]);

        $response->assertRedirect();

        $submission->refresh();
        $this->assertEquals('pending_kepala', $submission->status);
        $this->assertEquals($kepalaRole->id, $submission->current_role_id);
    }

    public function test_approver_can_reject_submission(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $response = $this->actingAs($spv)->post(route('approvals.reject', $submission), [
            'action' => 'reject',
            'notes' => 'Budget too high',
        ]);

        $response->assertRedirect();

        $submission->refresh();
        $this->assertEquals('draft', $submission->status);
        $this->assertEquals('Budget too high', $submission->rejection_reason);
    }
}
