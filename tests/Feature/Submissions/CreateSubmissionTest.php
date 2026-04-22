<?php

namespace Tests\Feature\Submissions;

use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_requestor_can_create_submission_with_files(): void
    {
        Storage::fake('public');

        // Create role chain for workflow
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [
            UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('document2.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('document3.pdf', 100, 'application/pdf'),
        ];

        $response = $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertRedirect();

        // Submission should be auto-submitted (not draft)
        $this->assertDatabaseHas('submissions', [
            'warehouse_name' => 'Test Warehouse',
            'status' => 'pending_spv',
        ]);

        $submission = Submission::where('warehouse_name', 'Test Warehouse')->first();
        $this->assertNotNull($submission);

        // Verify it advanced to SPV role
        $this->assertEquals($spvRole->id, $submission->current_role_id);

        // Verify SubmissionFile records created
        $this->assertDatabaseHas('submission_files', [
            'submission_id' => $submission->id,
            'file_name' => 'document1.pdf',
            'file_type' => 'application/pdf',
        ]);
        $this->assertEquals(3, SubmissionFile::where('submission_id', $submission->id)->count());
    }

    public function test_submission_files_contain_correct_metadata(): void
    {
        Storage::fake('public');

        // Create role chain for workflow
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        // File size in KB for UploadedFile::fake()
        $files = [
            UploadedFile::fake()->create('test.png', 100, 'image/png'),
            UploadedFile::fake()->create('report.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('specs.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $submission = Submission::where('warehouse_name', 'Test Warehouse')->first();
        $this->assertNotNull($submission);

        $savedFiles = SubmissionFile::where('submission_id', $submission->id)->get();

        $this->assertCount(3, $savedFiles);

        $pngFile = $savedFiles->firstWhere('file_name', 'test.png');
        $this->assertEquals('image/png', $pngFile->file_type);
        $this->assertGreaterThan(0, $pngFile->file_size);

        $jpgFile = $savedFiles->firstWhere('file_name', 'report.jpg');
        $this->assertEquals('image/jpeg', $jpgFile->file_type);
        $this->assertGreaterThan(0, $jpgFile->file_size);
    }

    public function test_submission_requires_minimum_3_files(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [
            UploadedFile::fake()->create('doc1.pdf', 100),
            UploadedFile::fake()->create('doc2.pdf', 100),
        ];

        $response = $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertSessionHasErrors('files');
    }

    public function test_submission_rejects_more_than_10_files(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [];
        for ($i = 1; $i <= 11; $i++) {
            $files[] = UploadedFile::fake()->create("doc{$i}.pdf", 100);
        }

        $response = $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertSessionHasErrors('files');
    }

    public function test_submission_rejects_invalid_file_types(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [
            UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('archive.zip', 100, 'application/zip'),
        ];

        $response = $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertSessionHasErrors(['files.2']);
    }

    public function test_submission_rejects_file_exceeding_5mb(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $requestorRole = Role::factory()->create(['name' => 'Requestor', 'next_role_id' => $spvRole->id]);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [
            UploadedFile::fake()->create('small.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('medium.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'), // Just over 5MB (5120KB)
        ];

        $response = $this->actingAs($user)->withSession(['2fa_verified' => true])->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertSessionHasErrors(['files.2']);
    }
}
