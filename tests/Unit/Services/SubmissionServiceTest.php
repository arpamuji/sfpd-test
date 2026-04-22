<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Services\SubmissionService;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class SubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SubmissionRepositoryInterface $submissionRepository;
    private SubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->submissionRepository = Mockery::mock(SubmissionRepositoryInterface::class);
        $this->service = new SubmissionService($this->submissionRepository);
    }

    public function test_create_submission_creates_draft(): void
    {
        $requestorRole = Role::create(['name' => 'Requestor']);
        $requestor = User::factory()->withRole($requestorRole->id)->create();

        $data = [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
        ];

        $this->submissionRepository
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(fn($d) => Submission::create(array_merge($d, [
                'requestor_id' => $requestor->id,
                'current_role_id' => $requestor->role_id,
            ])));

        $submission = $this->service->createSubmission($data, $requestor);

        $this->assertEquals('draft', $submission->status);
        $this->assertEquals('Test Warehouse', $submission->warehouse_name);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
