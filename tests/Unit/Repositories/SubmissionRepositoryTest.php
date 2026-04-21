<?php

namespace Tests\Unit\Repositories;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\SubmissionRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SubmissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SubmissionRepository();
    }

    public function test_find_returns_submission_with_relationships(): void
    {
        $requestorRole = \App\Models\Role::create(['name' => 'Requestor']);
        $requestor = User::factory()->withRole($requestorRole->id)->create();
        $currentRole = \App\Models\Role::create(['name' => 'SPV Gudang']);

        $submission = Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $currentRole->id,
        ]);

        $found = $this->repository->find($submission->id);

        $this->assertNotNull($found);
        $this->assertEquals($submission->id, $found->id);
        $this->assertTrue($found->relationLoaded('requestor'));
    }

    public function test_find_by_requestor_filters_by_user(): void
    {
        $requestorRole = \App\Models\Role::create(['name' => 'Requestor']);
        $user1 = User::factory()->withRole($requestorRole->id)->create();
        $user2 = User::factory()->withRole($requestorRole->id)->create();

        $currentRole = \App\Models\Role::create(['name' => 'SPV Gudang']);

        Submission::factory()->count(3)->create([
            'requestor_id' => $user1->id,
            'current_role_id' => $currentRole->id,
        ]);
        Submission::factory()->count(2)->create([
            'requestor_id' => $user2->id,
            'current_role_id' => $currentRole->id,
        ]);

        $result = $this->repository->findByRequestor($user1);

        $this->assertEquals(3, $result->total());
    }
}
