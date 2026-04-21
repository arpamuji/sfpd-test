# Phase 3: Auth & Controllers - Detailed Specification

**Tasks:** 11-13  
**Branch:** `feature/*` → `development`

---

## Task 11: Middleware

**Branch:** `feature/middleware` (from `development`)

**Files:**
- Create: `app/Http/Middleware/Ensure2FAEnabled.php`
- Create: `app/Http/Middleware/RoleCheck.php`

### Steps

- [ ] **Step 1: Create Ensure2FAEnabled middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Ensure2FAEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->google2fa_enabled) {
            return redirect()->route('2fa.setup');
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Create RoleCheck middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleCheck
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role->name !== $role) {
            abort(403, 'Unauthorized action for this role.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 3: Register middleware in Kernel.php**

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    // ...
    '2fa' => \App\Http\Middleware\Ensure2FAEnabled::class,
    'role' => \App\Http\Middleware\RoleCheck::class,
];
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Middleware/ app/Http/Kernel.php
git commit -m "feat(middleware): add 2FA and role check middleware"
```

---

## Task 12: Auth Controllers

**Branch:** `feature/auth-controllers` (from `development`)

**Files:**
- Create: `app/Http/Controllers/Auth/LoginController.php`
- Create: `app/Http/Controllers/Auth/TwoFactorController.php`

### Steps

- [ ] **Step 1: Create LoginController**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->google2fa_enabled) {
                return redirect()->route('2fa.verify');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
```

- [ ] **Step 2: Create TwoFactorController**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorRequest;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TwoFactorController extends Controller
{
    public function __construct(
        private TwoFactorAuthService $twoFactorService
    ) {}

    public function showVerification(): Response
    {
        return Inertia::render('Auth/TwoFactorVerify');
    }

    public function verify(TwoFactorRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $secret = $user->google2fa_secret;
        $code = $request->input('code');

        if ($this->twoFactorService->verifyCode($secret, $code)) {
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'code' => 'Invalid authentication code.',
        ]);
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/Auth/
git commit -m "feat(auth): add login and 2FA verification controllers"
```

---

## Task 13: Submission Controllers

**Branch:** `feature/submission-controllers` (from `development`)

**Files:**
- Create: `app/Http/Controllers/Submissions/SubmissionController.php`
- Create: `app/Http/Controllers/Approvals/ApprovalController.php`
- Create: `app/Http/Controllers/DashboardController.php`

### Steps

- [ ] **Step 1: Create SubmissionController**

```php
<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService
    ) {}

    public function index(): Response
    {
        $submissions = $this->submissionService->getMySubmissions(Auth::user());

        return Inertia::render('Submissions/Index', [
            'submissions' => $submissions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Submissions/Create');
    }

    public function store(StoreSubmissionRequest $request): RedirectResponse
    {
        $submission = $this->submissionService->createSubmission(
            $request->validated(),
            Auth::user()
        );

        return redirect()->route('submissions.show', $submission->id)
            ->with('success', 'Submission created successfully.');
    }

    public function show(Submission $submission): Response
    {
        $submission->load(['files', 'approvalLogs.approver', 'currentRole']);

        return Inertia::render('Submissions/Show', [
            'submission' => $submission,
        ]);
    }
}
```

- [ ] **Step 2: Create ApprovalController**

```php
<?php

namespace App\Http\Controllers\Approvals;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveSubmissionRequest;
use App\Models\Submission;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalWorkflowService $workflowService
    ) {}

    public function pending(): Response
    {
        $user = Auth::user();
        $submissions = $this->workflowService->getPendingForRole($user->role_id);

        return Inertia::render('Dashboard', [
            'submissions' => $submissions,
        ]);
    }

    public function approve(ApproveSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->workflowService->canApprove($submission, $user)) {
            abort(403, 'You cannot approve this submission.');
        }

        $updated = $this->workflowService->approve(
            $submission,
            $user,
            $request->input('notes')
        );

        return redirect()->route('dashboard')
            ->with('success', 'Submission approved.');
    }

    public function reject(ApproveSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $user = Auth::user();

        if (!$this->workflowService->canApprove($submission, $user)) {
            abort(403, 'You cannot reject this submission.');
        }

        $updated = $this->workflowService->reject(
            $submission,
            $user,
            $request->input('notes')
        );

        return redirect()->route('dashboard')
            ->with('success', 'Submission rejected.');
    }
}
```

- [ ] **Step 3: Create DashboardController**

```php
<?php

namespace App\Http\Controllers;

use App\Services\SubmissionService;
use App\Services\ApprovalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private SubmissionService $submissionService,
        private ApprovalWorkflowService $workflowService
    ) {}

    public function index(): Response
    {
        $user = Auth::user();
        
        $mySubmissions = $this->submissionService->getMySubmissions($user);
        $pendingApprovals = $this->workflowService->getPendingForRole($user->role_id);

        return Inertia::render('Dashboard/Dashboard', [
            'mySubmissions' => $mySubmissions,
            'pendingApprovals' => $pendingApprovals,
        ]);
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/
git commit -m "feat(controllers): add submission and approval controllers"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 11 Verification (Middleware)

- [ ] `app/Http/Middleware/Ensure2FAEnabled.php` exists
- [ ] `Ensure2FAEnabled` redirects to login if user not authenticated
- [ ] `Ensure2FAEnabled` redirects to `2fa.setup` if `google2fa_enabled` is false
- [ ] `app/Http/Middleware/RoleCheck.php` exists
- [ ] `RoleCheck` aborts with 403 if user's role doesn't match required role
- [ ] Both middleware registered in `app/Http/Kernel.php` as `'2fa'` and `'role'`

```bash
# Verification commands
test -f app/Http/Middleware/Ensure2FAEnabled.php && echo "✓ Ensure2FAEnabled exists"
test -f app/Http/Middleware/RoleCheck.php && echo "✓ RoleCheck exists"
grep -q "'2fa'" app/Http/Kernel.php && echo "✓ 2fa middleware registered"
grep -q "'role'" app/Http/Kernel.php && echo "✓ role middleware registered"
grep -q "google2fa_enabled" app/Http/Middleware/Ensure2FAEnabled.php && echo "✓ checks 2FA enabled status"
```

### Task 12 Verification (Auth Controllers)

- [ ] `app/Http/Controllers/Auth/LoginController.php` exists
- [ ] `LoginController` has `showLoginForm()`, `login()`, `logout()` methods
- [ ] `login()` uses `LoginRequest` validation
- [ ] `login()` redirects to `2fa.verify` if user has 2FA enabled
- [ ] `login()` redirects to `dashboard` if 2FA not enabled
- [ ] `logout()` invalidates session and regenerates token
- [ ] `app/Http/Controllers/Auth/TwoFactorController.php` exists
- [ ] `TwoFactorController` has `showVerification()`, `verify()` methods
- [ ] `verify()` uses `TwoFactorRequest` validation
- [ ] `verify()` calls `TwoFactorAuthService::verifyCode()`

```bash
# Verification commands
test -f app/Http/Controllers/Auth/LoginController.php && echo "✓ LoginController exists"
test -f app/Http/Controllers/Auth/TwoFactorController.php && echo "✓ TwoFactorController exists"
grep -q "showLoginForm" app/Http/Controllers/Auth/LoginController.php && echo "✓ has showLoginForm method"
grep -q "google2fa_enabled" app/Http/Controllers/Auth/LoginController.php && echo "✓ checks 2FA in login"
grep -q "verifyCode" app/Http/Controllers/Auth/TwoFactorController.php && echo "✓ verifies 2FA code"
```

### Task 13 Verification (Submission Controllers)

- [ ] `app/Http/Controllers/Submissions/SubmissionController.php` exists
- [ ] `SubmissionController` has `index()`, `create()`, `store()`, `show()` methods
- [ ] `store()` uses `StoreSubmissionRequest` validation
- [ ] `store()` calls `SubmissionService::createSubmission()`
- [ ] `app/Http/Controllers/Approvals/ApprovalController.php` exists
- [ ] `ApprovalController` has `pending()`, `approve()`, `reject()` methods
- [ ] `approve()` and `reject()` use `ApproveSubmissionRequest` validation
- [ ] `approve()` checks `canApprove()` before proceeding
- [ ] `app/Http/Controllers/DashboardController.php` exists
- [ ] `DashboardController` has `index()` method showing submissions and pending approvals

```bash
# Verification commands
test -f app/Http/Controllers/Submissions/SubmissionController.php && echo "✓ SubmissionController exists"
test -f app/Http/Controllers/Approvals/ApprovalController.php && echo "✓ ApprovalController exists"
test -f app/Http/Controllers/DashboardController.php && echo "✓ DashboardController exists"
grep -q "StoreSubmissionRequest" app/Http/Controllers/Submissions/SubmissionController.php && echo "✓ uses form request validation"
grep -q "canApprove" app/Http/Controllers/Approvals/ApprovalController.php && echo "✓ checks approval permission"
```

### Branch State Verification

- [ ] Feature branches merged: `feature/middleware`, `feature/auth-controllers`, `feature/submission-controllers`
- [ ] Current branch is `development`
- [ ] Commits exist for middleware and controllers
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="feat(middleware)" | head -1 && echo "✓ middleware committed"
git log --oneline --grep="feat(auth)" | head -1 && echo "✓ auth controllers committed"
git log --oneline --grep="feat(controllers)" | head -1 && echo "✓ submission controllers committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-4-validation-routes.md](phase-4-validation-routes.md)
