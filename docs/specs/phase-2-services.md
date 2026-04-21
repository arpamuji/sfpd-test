# Phase 2: Backend Services - Detailed Specification

**Tasks:** 8-10  
**Branch:** `feature/services` → `development`

---

## Task 8: SubmissionService

**Branch:** `feature/services` (from `development`)

**File:** `app/Services/SubmissionService.php`

### Steps

- [ ] **Step 1: Create SubmissionService**

```php
<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function __construct(
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    public function createSubmission(array $data, User $requestor): Submission
    {
        return DB::transaction(function () use ($data, $requestor) {
            $submission = $this->submissionRepository->create([
                'requestor_id' => $requestor->id,
                'warehouse_name' => $data['warehouse_name'],
                'warehouse_address' => $data['warehouse_address'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'budget_estimate' => $data['budget_estimate'],
                'description' => $data['description'] ?? null,
                'status' => 'draft',
            ]);

            return $submission;
        });
    }

    public function updateSubmission(Submission $submission, array $data): Submission
    {
        return $this->submissionRepository->update($submission, $data);
    }

    public function getSubmission(string $id): ?Submission
    {
        return $this->submissionRepository->find($id);
    }

    public function getMySubmissions(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->findByRequestor($user);
    }

    public function getMyDrafts(User $user): LengthAwarePaginator
    {
        return $this->submissionRepository->getDraftsByRequestor($user);
    }

    public function submitForApproval(Submission $submission): Submission
    {
        $firstApproverRole = $submission->currentRole;

        return $this->submissionRepository->update($submission, [
            'status' => 'pending_spv',
            'submitted_at' => now(),
        ]);
    }
}
```

---

## Task 9: ApprovalWorkflowService

**File:** `app/Services/ApprovalWorkflowService.php`

### Steps

- [ ] **Step 1: Create ApprovalWorkflowService**

```php
<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\Contracts\SubmissionRepositoryInterface;

class ApprovalWorkflowService
{
    public function __construct(
        private ApprovalRepositoryInterface $approvalRepository,
        private SubmissionRepositoryInterface $submissionRepository
    ) {}

    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        return $this->approvalRepository->approve($submission, $approver, $notes);
    }

    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        return $this->approvalRepository->reject($submission, $approver, $notes);
    }

    public function getPendingForRole(string $roleId): array
    {
        $role = Role::find($roleId);
        if (!$role) {
            return [];
        }

        return $role->submissionsAtThisLevel()
            ->whereNotIn('status', ['draft', 'approved', 'rejected'])
            ->latest()
            ->get();
    }

    public function canApprove(Submission $submission, User $user): bool
    {
        return $submission->current_role_id === $user->role_id;
    }
}
```

---

## Task 10: TwoFactorAuthService

**File:** `app/Services/TwoFactorAuthService.php`

### Steps

- [ ] **Step 1: Create TwoFactorAuthService**

```php
<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorAuthService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQRCodeDataUrl(string $email, string $secret): string
    {
        $companyName = config('app.name');
        $secret = $this->google2fa->generateSecretKey();
        
        $qrUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $email,
            $secret
        );

        $writer = new Writer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );

        return $writer->writeString($qrUrl);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function enable2FA(User $user, string $secret): void
    {
        $user->update([
            'google2fa_secret' => $secret,
            'google2fa_enabled' => true,
        ]);
    }

    public function disable2FA(User $user): void
    {
        $user->update([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);
    }
}
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 8 Verification (SubmissionService)

- [ ] `app/Services/SubmissionService.php` exists
- [ ] Constructor injects `SubmissionRepositoryInterface`
- [ ] `createSubmission()` method creates submission with 'draft' status inside DB transaction
- [ ] `updateSubmission()` delegates to repository
- [ ] `getSubmission()` returns submission with relationships loaded
- [ ] `getMySubmissions()` calls repository `findByRequestor()`
- [ ] `getMyDrafts()` calls repository `getDraftsByRequestor()`
- [ ] `submitForApproval()` updates status to 'pending_spv' and sets `submitted_at`

```bash
# Verification commands
test -f app/Services/SubmissionService.php && echo "✓ SubmissionService exists"
grep -q "SubmissionRepositoryInterface" app/Services/SubmissionService.php && echo "✓ uses repository interface"
grep -q "DB::transaction" app/Services/SubmissionService.php && echo "✓ uses transactions"
grep -q "createSubmission" app/Services/SubmissionService.php && echo "✓ has createSubmission method"
```

### Task 9 Verification (ApprovalWorkflowService)

- [ ] `app/Services/ApprovalWorkflowService.php` exists
- [ ] Constructor injects both `ApprovalRepositoryInterface` and `SubmissionRepositoryInterface`
- [ ] `approve()` delegates to repository
- [ ] `reject()` delegates to repository
- [ ] `getPendingForRole()` returns submissions for role excluding draft/approved/rejected
- [ ] `canApprove()` compares `submission->current_role_id` with `user->role_id`

```bash
# Verification commands
test -f app/Services/ApprovalWorkflowService.php && echo "✓ ApprovalWorkflowService exists"
grep -q "canApprove" app/Services/ApprovalWorkflowService.php && echo "✓ has canApprove method"
grep -q "getPendingForRole" app/Services/ApprovalWorkflowService.php && echo "✓ has getPendingForRole method"
```

### Task 10 Verification (TwoFactorAuthService)

- [ ] `app/Services/TwoFactorAuthService.php` exists
- [ ] Uses `PragmaRX\Google2FA\Google2FA` class
- [ ] Uses `BaconQrCode\Writer` for QR code generation
- [ ] `generateSecret()` returns key from Google2FA
- [ ] `getQRCodeDataUrl()` generates SVG QR code
- [ ] `verifyCode()` validates TOTP code against secret
- [ ] `enable2FA()` updates user with encrypted secret
- [ ] `disable2FA()` clears user's 2FA settings

```bash
# Verification commands
test -f app/Services/TwoFactorAuthService.php && echo "✓ TwoFactorAuthService exists"
grep -q "Google2FA" app/Services/TwoFactorAuthService.php && echo "✓ uses Google2FA library"
grep -q "BaconQrCode" app/Services/TwoFactorAuthService.php && echo "✓ uses QR code library"
grep -q "verifyCode" app/Services/TwoFactorAuthService.php && echo "✓ has verifyCode method"
grep -q "generateSecret" app/Services/TwoFactorAuthService.php && echo "✓ has generateSecret method"
```

### Service Registration Verification

- [ ] All three services are instantiable via service container
- [ ] Services can be resolved with `app()` helper

```bash
# Verification commands
php artisan tinker --execute="app(\App\Services\SubmissionService::class)" && echo "✓ SubmissionService resolvable"
php artisan tinker --execute="app(\App\Services\ApprovalWorkflowService::class)" && echo "✓ ApprovalWorkflowService resolvable"
php artisan tinker --execute="app(\App\Services\TwoFactorAuthService::class)" && echo "✓ TwoFactorAuthService resolvable"
```

### Branch State Verification

- [ ] Feature branch `feature/services` created and merged to `development`
- [ ] Current branch is `development`
- [ ] Commit exists with message containing `feat(services):`
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="feat(services)" | head -1 && echo "✓ services committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-3-auth-controllers.md](phase-3-auth-controllers.md)
