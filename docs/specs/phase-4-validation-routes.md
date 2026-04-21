# Phase 4: Validation & Routes - Detailed Specification

**Tasks:** 14-15  
**Branch:** `feature/validation`, `feature/routes` → `development`

---

## Task 14: Form Requests

**Branch:** `feature/validation` (from `development`)

**Files:**
- Create: `app/Http/Requests/LoginRequest.php`
- Create: `app/Http/Requests/TwoFactorRequest.php`
- Create: `app/Http/Requests/StoreSubmissionRequest.php`
- Create: `app/Http/Requests/ApproveSubmissionRequest.php`

### Steps

- [ ] **Step 1: Create LoginRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'These credentials do not match our records.',
        ];
    }
}
```

- [ ] **Step 2: Create TwoFactorRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.digits' => 'The authentication code must be 6 digits.',
            'code.numeric' => 'The authentication code must be a number.',
        ];
    }
}
```

- [ ] **Step 3: Create StoreSubmissionRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'warehouse_name' => ['required', 'string', 'max:255'],
            'warehouse_address' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'budget_estimate' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'files' => ['required', 'array', 'min:3'],
            'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'files.min' => 'At least 3 documents are required (minimum 3 PDFs).',
            'files.*.mimes' => 'Files must be PDF, JPG, JPEG, or PNG.',
            'files.*.max' => 'Files must not exceed 5MB.',
            'latitude.between' => 'Latitude must be between -90 and 90.',
            'longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }
}
```

- [ ] **Step 4: Create ApproveSubmissionRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'notes' => ['required_if:action,reject', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required_if' => 'A rejection reason is required.',
            'notes.max' => 'The rejection reason must not exceed 1000 characters.',
        ];
    }
}
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Requests/
git commit -m "feat(validation): add form request validation classes"
```

---

## Task 15: Routes

**Branch:** `feature/routes` (from `development`)

**Files:**
- Modify: `routes/web.php`
- Create: `routes/auth.php`

### Steps

- [ ] **Step 1: Create auth.php routes**

```php
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('2fa/verify', [TwoFactorController::class, 'showVerification'])->name('2fa.verify');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify']);
    
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
```

- [ ] **Step 2: Create web.php routes**

```php
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Submissions\SubmissionController;
use App\Http\Controllers\Approvals\ApprovalController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware(['auth', '2fa'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Pending approvals (for approvers)
    Route::get('/approvals/pending', [ApprovalController::class, 'pending'])
        ->name('approvals.pending');
    Route::post('/approvals/{submission}/approve', [ApprovalController::class, 'approve'])
        ->name('approvals.approve');
    Route::post('/approvals/{submission}/reject', [ApprovalController::class, 'reject'])
        ->name('approvals.reject');
    
    // Submissions
    Route::get('/submissions', [SubmissionController::class, 'index'])
        ->name('submissions.index');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])
        ->name('submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])
        ->name('submissions.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('submissions.show');
});
```

- [ ] **Step 3: Commit**

```bash
git add routes/
git commit -m "feat(routes): define auth and submission routes"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 14 Verification (Form Requests)

- [ ] `app/Http/Requests/LoginRequest.php` exists
- [ ] `LoginRequest` has rules for `email` (required, email, exists), `password` (required), `remember` (nullable, boolean)
- [ ] `app/Http/Requests/TwoFactorRequest.php` exists
- [ ] `TwoFactorRequest` has rules for `code` (required, digits:6, numeric)
- [ ] `app/Http/Requests/StoreSubmissionRequest.php` exists
- [ ] `StoreSubmissionRequest` has rules for:
  - `warehouse_name`, `warehouse_address` (required)
  - `latitude` (required, numeric, between:-90,90)
  - `longitude` (required, numeric, between:-180,180)
  - `budget_estimate` (required, numeric, min:0)
  - `files` (required, array, min:3), `files.*` (file, mimes:pdf,jpg,jpeg,png, max:5120)
- [ ] `app/Http/Requests/ApproveSubmissionRequest.php` exists
- [ ] `ApproveSubmissionRequest` has `action` (required, in:approve,reject), `notes` (required_if:action,reject)

```bash
# Verification commands
test -f app/Http/Requests/LoginRequest.php && echo "✓ LoginRequest exists"
test -f app/Http/Requests/TwoFactorRequest.php && echo "✓ TwoFactorRequest exists"
test -f app/Http/Requests/StoreSubmissionRequest.php && echo "✓ StoreSubmissionRequest exists"
test -f app/Http/Requests/ApproveSubmissionRequest.php && echo "✓ ApproveSubmissionRequest exists"
grep -q "digits:6" app/Http/Requests/TwoFactorRequest.php && echo "✓ TwoFactorRequest validates 6 digits"
grep -q "min:3" app/Http/Requests/StoreSubmissionRequest.php && echo "✓ StoreSubmissionRequest requires min 3 files"
grep -q "required_if:action,reject" app/Http/Requests/ApproveSubmissionRequest.php && echo "✓ ApproveSubmissionRequest requires notes for reject"
```

### Task 15 Verification (Routes)

- [ ] `routes/auth.php` exists
- [ ] `routes/auth.php` defines:
  - GET/POST `login` (guest middleware)
  - GET/POST `2fa/verify` (auth, 2fa middleware)
  - POST `logout` (auth, 2fa middleware)
- [ ] `routes/web.php` exists
- [ ] `routes/web.php` requires `auth.php`
- [ ] `routes/web.php` defines (all under `auth`, `2fa` middleware):
  - GET `/dashboard` → `DashboardController@index`
  - GET `/approvals/pending` → `ApprovalController@pending`
  - POST `/approvals/{submission}/approve` → `ApprovalController@approve`
  - POST `/approvals/{submission}/reject` → `ApprovalController@reject`
  - GET `/submissions` → `SubmissionController@index`
  - GET `/submissions/create` → `SubmissionController@create`
  - POST `/submissions` → `SubmissionController@store`
  - GET `/submissions/{submission}` → `SubmissionController@show`
- [ ] All routes have named route definitions

```bash
# Verification commands
test -f routes/auth.php && echo "✓ auth.php exists"
test -f routes/web.php && echo "✓ web.php exists"
grep -q "2fa/verify" routes/auth.php && echo "✓ 2fa verify route defined"
grep -q "approvals.pending" routes/web.php && echo "✓ approvals.pending route defined"
grep -q "submissions.index" routes/web.php && echo "✓ submissions.index route defined"
php artisan route:list --path=dashboard | grep -q "dashboard" && echo "✓ dashboard route registered"
php artisan route:list --path=submissions | grep -q "submissions" && echo "✓ submission routes registered"
php artisan route:list --path=approvals | grep -q "approvals" && echo "✓ approval routes registered"
```

### Branch State Verification

- [ ] Feature branches merged: `feature/validation`, `feature/routes`
- [ ] Current branch is `development`
- [ ] Commits exist for validation and routes
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="feat(validation)" | head -1 && echo "✓ validation committed"
git log --oneline --grep="feat(routes)" | head -1 && echo "✓ routes committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-5-testing.md](phase-5-testing.md)
