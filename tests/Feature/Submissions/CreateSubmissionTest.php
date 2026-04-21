<?php

namespace Tests\Feature\Submissions;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_requestor_can_create_submission(): void
    {
        Storage::fake('private');

        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $files = [
            UploadedFile::fake()->create('document1.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('document2.pdf', 100, 'application/pdf'),
            UploadedFile::fake()->create('document3.pdf', 100, 'application/pdf'),
        ];

        $response = $this->actingAs($user)->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => $files,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'warehouse_name' => 'Test Warehouse',
            'status' => 'draft',
        ]);
    }

    public function test_submission_requires_minimum_3_files(): void
    {
        $requestorRole = Role::factory()->create(['name' => 'Requestor']);
        $user = User::factory()->withRole($requestorRole->id)->create(['google2fa_enabled' => true]);

        $response = $this->actingAs($user)->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => [
                UploadedFile::fake()->create('doc1.pdf', 100),
                UploadedFile::fake()->create('doc2.pdf', 100),
            ],
        ]);

        $response->assertSessionHasErrors('files');
    }
}
