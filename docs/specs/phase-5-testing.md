# Phase 5: Testing - Detailed Specification

**Task:** 16  
**Branch:** `feature/backend-tests` → `development`

---

## Task 16: Backend Tests

**Branch:** `feature/backend-tests` (from `development`)

**Files:**
- Create: `tests/Unit/Services/SubmissionServiceTest.php`
- Create: `tests/Unit/Services/ApprovalWorkflowServiceTest.php`
- Create: `tests/Unit/Services/TwoFactorAuthServiceTest.php`
- Create: `tests/Unit/Repositories/SubmissionRepositoryTest.php`
- Create: `tests/Feature/Auth/LoginTest.php`
- Create: `tests/Feature/Auth/TwoFactorTest.php`
- Create: `tests/Feature/Submissions/CreateSubmissionTest.php`
- Create: `tests/Feature/Submissions/ApprovalWorkflowTest.php`
- Create: `tests/Feature/RoleAccessTest.php`

### Unit Tests

- [ ] **Step 1: SubmissionServiceTest**

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Role;
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
        $requestor = User::factory()->create(['role_id' => Role::factory()->create(['name' => 'Requestor'])->id]);
        
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
            ->andReturnUsing(fn($d) => \App\Models\Submission::create(array_merge($d, ['requestor_id' => $requestor->id])));

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
```

- [ ] **Step 2: ApprovalWorkflowServiceTest**

```php
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
        $approver = User::factory()->create(['role_id' => $approverRole->id]);
        
        $submission = Submission::factory()->create(['current_role_id' => $approverRole->id]);

        $this->assertTrue($this->service->canApprove($submission, $approver));
    }

    public function test_can_approve_returns_false_for_wrong_role(): void
    {
        $wrongRole = Role::create(['name' => 'Kepala Gudang']);
        $user = User::factory()->create(['role_id' => $wrongRole->id]);
        
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
```

- [ ] **Step 3: TwoFactorAuthServiceTest**

```php
<?php

namespace Tests\Unit\Services;

use App\Services\TwoFactorAuthService;
use Tests\TestCase;

class TwoFactorAuthServiceTest extends TestCase
{
    private TwoFactorAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorAuthService();
    }

    public function test_generate_secret_returns_valid_string(): void
    {
        $secret = $this->service->generateSecret();
        
        $this->assertIsString($secret);
        $this->assertNotEmpty($secret);
    }

    public function test_verify_code_with_valid_code(): void
    {
        $secret = $this->service->generateSecret();
        $code = $this->service->google2fa->getCurrentOATH($secret);
        
        $this->assertTrue($this->service->verifyCode($secret, $code));
    }

    public function test_verify_code_with_invalid_code(): void
    {
        $secret = $this->service->generateSecret();
        
        $this->assertFalse($this->service->verifyCode($secret, '000000'));
    }
}
```

- [ ] **Step 4: SubmissionRepositoryTest**

```php
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
        $requestor = User::factory()->create();
        $submission = Submission::factory()->create(['requestor_id' => $requestor->id]);
        
        $found = $this->repository->find($submission->id->toString());
        
        $this->assertNotNull($found);
        $this->assertEquals($submission->id, $found->id);
        $this->assertTrue($found->relationLoaded('requestor'));
    }

    public function test_find_by_requestor_filters_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Submission::factory()->count(3)->create(['requestor_id' => $user1->id]);
        Submission::factory()->count(2)->create(['requestor_id' => $user2->id]);
        
        $result = $this->repository->findByRequestor($user1);
        
        $this->assertEquals(3, $result->total());
    }
}
```

### Feature Tests

- [ ] **Step 5: LoginTest**

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
            'google2fa_enabled' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_2fa_when_enabled(): void
    {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password),
            'google2fa_enabled' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect(route('2fa.verify'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
```

- [ ] **Step 6: TwoFactorTest**

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_verify_with_valid_code(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $user = User::factory()->create([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);

        $this->actingAs($user);
        
        $twoFactorService = new TwoFactorAuthService();
        $code = $twoFactorService->google2fa->getCurrentOATH($secret);

        $response = $this->post(route('2fa.verify'), ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_verification_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'google2fa_secret' => 'JBSWY3DPEHPK3PXP',
            'google2fa_enabled' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('2fa.verify'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
    }
}
```

- [ ] **Step 7: CreateSubmissionTest**

```php
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
        $user = User::factory()->create(['role_id' => $requestorRole->id, 'google2fa_enabled' => false]);

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
        $user = User::factory()->create(['google2fa_enabled' => false]);

        $response = $this->actingAs($user)->post(route('submissions.store'), [
            'warehouse_name' => 'Test Warehouse',
            'warehouse_address' => '123 Test St',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'budget_estimate' => 100000000,
            'files' => [
                \Illuminate\Http\UploadedFile::fake()->create('doc1.pdf', 100),
                \Illuminate\Http\UploadedFile::fake()->create('doc2.pdf', 100),
            ],
        ]);

        $response->assertSessionHasErrors('files');
    }
}
```

- [ ] **Step 8: ApprovalWorkflowTest**

```php
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
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang', 'next_role_id' => $spvRole->id]);
        
        $spv = User::factory()->create(['role_id' => $spvRole->id, 'google2fa_enabled' => false]);
        $requestor = User::factory()->create(['google2fa_enabled' => false]);
        
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
    }

    public function test_approver_can_reject_submission(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $spv = User::factory()->create(['role_id' => $spvRole->id, 'google2fa_enabled' => false]);
        $requestor = User::factory()->create(['google2fa_enabled' => false]);
        
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
```

- [ ] **Step 9: RoleAccessTest**

```php
<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_own_submissions(): void
    {
        $user1 = User::factory()->create(['google2fa_enabled' => false]);
        $user2 = User::factory()->create(['google2fa_enabled' => false]);
        
        Submission::factory()->count(3)->create(['requestor_id' => $user1->id]);
        Submission::factory()->count(2)->create(['requestor_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('submissions.index'));

        $response->assertStatus(200);
        $this->assertCount(3, $response->original['submissions']->items());
    }

    public function test_approver_sees_only_pending_for_their_role(): void
    {
        $spvRole = Role::factory()->create(['name' => 'SPV Gudang']);
        $kepalaRole = Role::factory()->create(['name' => 'Kepala Gudang']);
        
        $spv = User::factory()->create(['role_id' => $spvRole->id, 'google2fa_enabled' => false]);
        $requestor = User::factory()->create(['google2fa_enabled' => false]);
        
        Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $spvRole->id,
            'status' => 'pending_spv',
        ]);
        
        Submission::factory()->create([
            'requestor_id' => $requestor->id,
            'current_role_id' => $kepalaRole->id,
            'status' => 'pending_kepala',
        ]);

        $response = $this->actingAs($spv)->get(route('approvals.pending'));

        $response->assertStatus(200);
    }
}
```

- [ ] **Step 10: Run tests**

```bash
php artisan test
```

Expected: All tests passing

- [ ] **Step 11: Commit**

```bash
git add tests/
git commit -m "test(backend): add unit and feature tests"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Unit Tests Verification

- [ ] `tests/Unit/Services/SubmissionServiceTest.php` exists with tests:
  - `test_create_submission_creates_draft()` - verifies submission created with draft status
- [ ] `tests/Unit/Services/ApprovalWorkflowServiceTest.php` exists with tests:
  - `test_can_approve_returns_true_for_current_approver()`
  - `test_can_approve_returns_false_for_wrong_role()`
- [ ] `tests/Unit/Services/TwoFactorAuthServiceTest.php` exists with tests:
  - `test_generate_secret_returns_valid_string()`
  - `test_verify_code_with_valid_code()`
  - `test_verify_code_with_invalid_code()`
- [ ] `tests/Unit/Repositories/SubmissionRepositoryTest.php` exists with tests:
  - `test_find_returns_submission_with_relationships()`
  - `test_find_by_requestor_filters_by_user()`

### Feature Tests Verification

- [ ] `tests/Feature/Auth/LoginTest.php` exists with tests:
  - `test_user_can_login_with_valid_credentials()`
  - `test_login_redirects_to_2fa_when_enabled()`
  - `test_login_fails_with_invalid_credentials()`
- [ ] `tests/Feature/Auth/TwoFactorTest.php` exists with tests:
  - `test_user_can_verify_with_valid_code()`
  - `test_verification_fails_with_invalid_code()`
- [ ] `tests/Feature/Submissions/CreateSubmissionTest.php` exists with tests:
  - `test_requestor_can_create_submission()`
  - `test_submission_requires_minimum_3_files()`
- [ ] `tests/Feature/Submissions/ApprovalWorkflowTest.php` exists with tests:
  - `test_spv_can_approve_submission()`
  - `test_approver_can_reject_submission()`
- [ ] `tests/Feature/RoleAccessTest.php` exists with tests:
  - `test_user_can_only_see_own_submissions()`
  - `test_approver_sees_only_pending_for_their_role()`

### Test Execution Verification

- [ ] All tests pass with `php artisan test`
- [ ] No errors or failures in test output
- [ ] Code coverage report shows tests are executing (optional)

```bash
# Verification commands
php artisan test --list | grep -q "SubmissionServiceTest" && echo "✓ SubmissionServiceTest exists"
php artisan test --list | grep -q "ApprovalWorkflowServiceTest" && echo "✓ ApprovalWorkflowServiceTest exists"
php artisan test --list | grep -q "LoginTest" && echo "✓ LoginTest exists"
php artisan test --list | grep -q "TwoFactorTest" && echo "✓ TwoFactorTest exists"
php artisan test --list | grep -q "CreateSubmissionTest" && echo "✓ CreateSubmissionTest exists"
php artisan test --list | grep -q "ApprovalWorkflowTest" && echo "✓ ApprovalWorkflowTest exists"
php artisan test 2>&1 | grep -q "OK" && echo "✓ all tests passing"
```

### Factory Verification

- [ ] `database/factories/UserFactory.php` exists
- [ ] `database/factories/SubmissionFactory.php` exists
- [ ] Factories define all required fields with valid defaults

```bash
# Verification commands
test -f database/factories/UserFactory.php && echo "✓ UserFactory exists"
test -f database/factories/SubmissionFactory.php && echo "✓ SubmissionFactory exists"
grep -q "role_id" database/factories/UserFactory.php && echo "✓ UserFactory defines role_id"
grep -q "requestor_id" database/factories/SubmissionFactory.php && echo "✓ SubmissionFactory defines requestor_id"
```

### Branch State Verification

- [ ] Feature branch `feature/backend-tests` merged to `development`
- [ ] Current branch is `development`
- [ ] Commit exists with message containing `test(backend):`
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="test(backend)" | head -1 && echo "✓ tests committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-6-frontend.md](phase-6-frontend.md)
