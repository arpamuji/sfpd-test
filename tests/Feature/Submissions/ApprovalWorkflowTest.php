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
        // Set up role chain: Requestor -> SPV Gudang
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole->update(['next_role_id' => $spvRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

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
        $this->assertEquals('rejected', $submission->status);
        $this->assertEquals('Budget too high', $submission->rejection_reason);
        $this->assertNotNull($submission->rejected_by);
    }

    public function test_rejection_requires_reason(): void
    {
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole->update(['next_role_id' => $spvRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        // Try to reject without notes
        $response = $this->actingAs($spv)->post(route('approvals.reject', $submission), [
            'action' => 'reject',
            'notes' => '',
        ]);

        $response->assertSessionHasErrors('notes');

        // Submission should not be rejected
        $submission->refresh();
        $this->assertEquals('pending_spv', $submission->status);
    }

    public function test_approval_does_not_require_notes(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);
        $spvRole->update(['next_role_id' => $kepalaRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        // Approve without notes - should succeed
        $response = $this->actingAs($spv)->post(route('approvals.approve', $submission), [
            'action' => 'approve',
            // No notes provided
        ]);

        $response->assertRedirect();

        $submission->refresh();
        $this->assertEquals('pending_kepala', $submission->status);
    }

    public function test_approval_log_is_created_on_approve(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);
        $spvRole->update(['next_role_id' => $kepalaRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $this->actingAs($spv)->post(route('approvals.approve', $submission), [
            'action' => 'approve',
            'notes' => 'Looks good',
        ]);

        $this->assertDatabaseHas('approval_logs', [
            'submission_id' => $submission->id,
            'approver_id' => $spv->id,
            'action' => 'approve',
            'notes' => 'Looks good',
        ]);
    }

    public function test_approval_log_is_created_on_reject(): void
    {
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole->update(['next_role_id' => $spvRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $this->actingAs($spv)->post(route('approvals.reject', $submission), [
            'action' => 'reject',
            'notes' => 'Budget too high',
        ]);

        $this->assertDatabaseHas('approval_logs', [
            'submission_id' => $submission->id,
            'approver_id' => $spv->id,
            'action' => 'reject',
            'notes' => 'Budget too high',
        ]);
    }

    public function test_rejection_reason_saved_to_database(): void
    {
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole->update(['next_role_id' => $spvRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $this->actingAs($spv)->post(route('approvals.reject', $submission), [
            'action' => 'reject',
            'notes' => 'Incomplete documentation',
        ]);

        $submission->refresh();
        $this->assertEquals('rejected', $submission->status);
        $this->assertEquals('Incomplete documentation', $submission->rejection_reason);
        $this->assertEquals($spv->id, $submission->rejected_by);
    }

    public function test_unauthorized_user_cannot_approve(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);
        $spvRole->update(['next_role_id' => $kepalaRole->id]);

        // User with wrong role tries to approve
        $wrongUser = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        $response = $this->actingAs($wrongUser)->post(route('approvals.approve', $submission), [
            'action' => 'approve',
        ]);

        // Should not approve - status should remain unchanged
        $submission->refresh();
        $this->assertEquals('pending_spv', $submission->status);
    }

    public function test_full_six_level_approval_chain_advances_correctly(): void
    {
        // Create full role chain as per requirements
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);
        $managerRole = Role::factory()->create(['name' => 'Manager Operasional']);
        $direkturOpsRole = Role::factory()->create(['name' => 'Direktur Operasional']);
        $direkturKeuanganRole = Role::factory()->create(['name' => 'Direktur Keuangan']);

        // Link the chain: Requestor -> SPV -> Kepala -> Manager -> Direktur Ops -> Direktur Keuangan
        $requestorRole->update(['next_role_id' => $spvRole->id]);
        $spvRole->update(['next_role_id' => $kepalaRole->id]);
        $kepalaRole->update(['next_role_id' => $managerRole->id]);
        $managerRole->update(['next_role_id' => $direkturOpsRole->id]);
        $direkturOpsRole->update(['next_role_id' => $direkturKeuanganRole->id]);

        // Create users for each role
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);
        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $kepala = User::factory()->withRole($kepalaRole->id)->create(['google2fa_enabled' => true]);
        $manager = User::factory()->withRole($managerRole->id)->create(['google2fa_enabled' => true]);
        $direkturOps = User::factory()->withRole($direkturOpsRole->id)->create(['google2fa_enabled' => true]);
        $direkturKeuangan = User::factory()->withRole($direkturKeuanganRole->id)->create(['google2fa_enabled' => true]);

        // Create submission starting at SPV level
        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        // Level 1: SPV approves
        $this->actingAs($spv)->post(route('approvals.approve', $submission), ['action' => 'approve']);
        $submission->refresh();
        $this->assertEquals('pending_kepala', $submission->status);
        $this->assertEquals($kepalaRole->id, $submission->current_role_id);

        // Level 2: Kepala approves
        $this->actingAs($kepala)->post(route('approvals.approve', $submission), ['action' => 'approve']);
        $submission->refresh();
        $this->assertEquals('pending_manager_ops', $submission->status);
        $this->assertEquals($managerRole->id, $submission->current_role_id);

        // Level 3: Manager approves
        $this->actingAs($manager)->post(route('approvals.approve', $submission), ['action' => 'approve']);
        $submission->refresh();
        $this->assertEquals('pending_direktur_ops', $submission->status);
        $this->assertEquals($direkturOpsRole->id, $submission->current_role_id);

        // Level 4: Direktur Ops approves
        $this->actingAs($direkturOps)->post(route('approvals.approve', $submission), ['action' => 'approve']);
        $submission->refresh();
        $this->assertEquals('pending_direktur_keuangan', $submission->status);
        $this->assertEquals($direkturKeuanganRole->id, $submission->current_role_id);

        // Level 5: Direktur Keuangan approves (final)
        $this->actingAs($direkturKeuangan)->post(route('approvals.approve', $submission), ['action' => 'approve']);
        $submission->refresh();
        $this->assertEquals('approved', $submission->status);
        $this->assertNotNull($submission->approved_at);
    }

    public function test_rejected_submission_cannot_be_resubmitted(): void
    {
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole->update(['next_role_id' => $spvRole->id]);

        $spv = User::factory()->withRole($spvRole->id)->create(['google2fa_enabled' => true]);
        $requestor = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);

        // SPV rejects - submission stays rejected
        $this->actingAs($spv)->post(route('approvals.reject', $submission), [
            'action' => 'reject',
            'notes' => 'Needs revision',
        ]);

        $submission->refresh();
        $this->assertEquals('rejected', $submission->status);
        $this->assertNotNull($submission->rejection_reason);

        // Rejected submission cannot be approved - should redirect back with error
        $response = $this->actingAs($spv)->post(route('approvals.approve', $submission), ['action' => 'approve']);

        $response->assertRedirect();
        $submission->refresh();
        $this->assertEquals('rejected', $submission->status);
    }
}
