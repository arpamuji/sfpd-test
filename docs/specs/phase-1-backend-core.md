# Phase 1: Backend Core - Detailed Specification

**Tasks:** 4-7  
**Branch:** `feature/*` → `development`

---

## Task 4: Database Migrations

**Branch:** `feature/db-migrations` (from `development`)

**Files:**
- Create: `database/migrations/0001_01_01_000001_create_roles_table.php`
- Create: `database/migrations/0001_01_01_000002_create_users_table.php`
- Create: `database/migrations/0001_01_01_000003_create_submissions_table.php`
- Create: `database/migrations/0001_01_01_000004_create_submission_files_table.php`
- Create: `database/migrations/0001_01_01_000005_create_approval_logs_table.php`

### Steps

- [ ] **Step 1: Create roles migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 50);
            $table->uuid('next_role_id')->nullable();
            $table->timestamps();
            
            $table->foreign('next_role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
```

- [ ] **Step 2: Create users migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('role_id')->constrained('roles');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('google2fa_secret')->nullable();
            $table->boolean('google2fa_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

- [ ] **Step 3: Create submissions migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requestor_id')->constrained('users');
            $table->foreignUuid('current_role_id')->constrained('roles');
            $table->string('status', 50)->default('draft');
            $table->string('warehouse_name');
            $table->text('warehouse_address');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('budget_estimate', 15, 2);
            $table->text('description')->nullable();
            $table->foreignUuid('rejected_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
```

- [ ] **Step 4: Create submission_files migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('file_type', 50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_files');
    }
};
```

- [ ] **Step 5: Create approval_logs migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('action', 10);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
```

- [ ] **Step 6: Run migrations**

```bash
php artisan migrate
```

Expected: All 5 tables created successfully

- [ ] **Step 7: Commit to feature branch**

```bash
git add database/migrations/
git commit -m "feat(db): create migrations with UUID primary keys and foreign keys"
```

- [ ] **Step 8: Merge to development**

```bash
git checkout development
git merge feature/db-migrations -m "merge: feature/db-migrations into development"
```

---

## Task 5: Models

**Branch:** `feature/models` (from `development`)

**Files:**
- Create: `app/Models/Role.php`
- Create: `app/Models/User.php`
- Create: `app/Models/Submission.php`
- Create: `app/Models/SubmissionFile.php`
- Create: `app/Models/ApprovalLog.php`

### Steps

- [ ] **Step 1: Create Role model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = ['name', 'next_role_id'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function nextRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'next_role_id');
    }

    public function submissionsAtThisLevel(): HasMany
    {
        return $this->hasMany(Submission::class, 'current_role_id');
    }

    public function isFinalApprover(): bool
    {
        return $this->next_role_id === null;
    }
}
```

- [ ] **Step 2: Create User model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'role_id',
        'email',
        'password',
        'google2fa_secret',
        'google2fa_enabled',
    ];

    protected $hidden = ['password', 'google2fa_secret'];

    protected function casts(): array
    {
        return [
            'google2fa_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'requestor_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'approver_id');
    }

    public function get2faSecretAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function set2faSecretAttribute(?string $value): void
    {
        $this->attributes['google2fa_secret'] = $value ? encrypt($value) : null;
    }
}
```

- [ ] **Step 3: Create Submission model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'requestor_id',
        'current_role_id',
        'status',
        'warehouse_name',
        'warehouse_address',
        'latitude',
        'longitude',
        'budget_estimate',
        'description',
        'rejected_by',
        'rejection_reason',
        'submitted_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'budget_estimate' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function currentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'current_role_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function isPending(): bool
    {
        return !in_array($this->status, ['approved', 'rejected', 'draft']);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
```

- [ ] **Step 4: Create SubmissionFile model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }
}
```

- [ ] **Step 5: Create ApprovalLog model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'submission_id',
        'approver_id',
        'action',
        'notes',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isApproval(): bool
    {
        return $this->action === 'approve';
    }

    public function isRejection(): bool
    {
        return $this->action === 'reject';
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add app/Models/
git commit -m "feat(models): create Eloquent models with UUID support and relationships"
```

- [ ] **Step 7: Merge to development**

```bash
git checkout development
git merge feature/models -m "merge: feature/models into development"
```

---

## Task 6: Seeders

**Branch:** `feature/seeders` (from `development`)

**Files:**
- Create: `database/seeders/RolesSeeder.php`
- Create: `database/seeders/UsersSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

### Steps

- [ ] **Step 1: Create RolesSeeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles in reverse order to set next_role_id
        $direkturKeuangan = Role::create([
            'name' => 'Direktur Keuangan',
            'next_role_id' => null,
        ]);

        $direkturOps = Role::create([
            'name' => 'Direktur Operasional',
            'next_role_id' => $direkturKeuangan->id,
        ]);

        $managerOps = Role::create([
            'name' => 'Manager Operasional',
            'next_role_id' => $direkturOps->id,
        ]);

        $kepalaGudang = Role::create([
            'name' => 'Kepala Gudang',
            'next_role_id' => $managerOps->id,
        ]);

        $spvGudang = Role::create([
            'name' => 'SPV Gudang',
            'next_role_id' => $kepalaGudang->id,
        ]);

        Role::create([
            'name' => 'Requestor',
            'next_role_id' => $spvGudang->id,
        ]);
    }
}
```

- [ ] **Step 2: Create UsersSeeder**

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    private const TEST_2FA_SECRETS = [
        'requestor' => 'JBSWY3DPEHPK3PXP',
        'spv' => 'KRSXG5CTMVRXEZLU',
        'kepala' => 'GEZDGNBVGY3TQOJQ',
        'manager' => 'MFRGGZDFMY2TQNZZ',
        'direktur_ops' => 'OVSG433SMVZWKZTH',
        'direktur_keuangan' => 'KRSXG5CTMVRXEZTB',
    ];

    public function run(): void
    {
        $roles = Role::all()->keyBy('name');
        $password = Hash::make('password123');

        $users = [
            ['email' => 'requestor@test.com', 'role' => 'Requestor', 'secret' => self::TEST_2FA_SECRETS['requestor']],
            ['email' => 'spv@test.com', 'role' => 'SPV Gudang', 'secret' => self::TEST_2FA_SECRETS['spv']],
            ['email' => 'kepala@test.com', 'role' => 'Kepala Gudang', 'secret' => self::TEST_2FA_SECRETS['kepala']],
            ['email' => 'manager@test.com', 'role' => 'Manager Operasional', 'secret' => self::TEST_2FA_SECRETS['manager']],
            ['email' => 'direktur.ops@test.com', 'role' => 'Direktur Operasional', 'secret' => self::TEST_2FA_SECRETS['direktur_ops']],
            ['email' => 'direktur.keuangan@test.com', 'role' => 'Direktur Keuangan', 'secret' => self::TEST_2FA_SECRETS['direktur_keuangan']],
        ];

        foreach ($users as $userData) {
            User::create([
                'email' => $userData['email'],
                'password' => $password,
                'role_id' => $roles[$userData['role']]->id,
                'google2fa_secret' => $userData['secret'],
                'google2fa_enabled' => true,
            ]);
        }

        $this->command->info("2FA Secrets for testing (include in README):");
        foreach ($users as $userData) {
            $this->command->info("  {$userData['role']}: {$userData['secret']}");
        }
    }
}
```

- [ ] **Step 3: Update DatabaseSeeder**

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
```

- [ ] **Step 4: Run seeders**

```bash
php artisan db:seed
```

Expected: 6 roles + 6 users created, 2FA secrets displayed

- [ ] **Step 5: Commit**

```bash
git add database/seeders/
git commit -m "feat(seeders): add roles and users seeders with 2FA secrets"
```

- [ ] **Step 6: Merge to development**

```bash
git checkout development
git merge feature/seeders -m "merge: feature/seeders into development"
```

---

## Task 7: Repositories

**Branch:** `feature/repositories` (from `development`)

**Files:**
- Create: `app/Repositories/Contracts/SubmissionRepositoryInterface.php`
- Create: `app/Repositories/Contracts/ApprovalRepositoryInterface.php`
- Create: `app/Repositories/SubmissionRepository.php`
- Create: `app/Repositories/ApprovalRepository.php`
- Modify: `app/Providers/AppServiceProvider.php`

### Steps

- [ ] **Step 1: Create SubmissionRepositoryInterface**

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubmissionRepositoryInterface
{
    public function create(array $data): Submission;
    
    public function update(Submission $submission, array $data): Submission;
    
    public function find(string $id): ?Submission;
    
    public function findByRequestor(User $user): LengthAwarePaginator;
    
    public function findByCurrentRole(string $roleId): LengthAwarePaginator;
    
    public function getDraftsByRequestor(User $user): LengthAwarePaginator;
}
```

- [ ] **Step 2: Create ApprovalRepositoryInterface**

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Submission;
use App\Models\User;

interface ApprovalRepositoryInterface
{
    public function approve(Submission $submission, User $approver, ?string $notes): Submission;
    
    public function reject(Submission $submission, User $approver, string $notes): Submission;
    
    public function getApprovalHistory(Submission $submission);
}
```

- [ ] **Step 3: Create SubmissionRepository**

```php
<?php

namespace App\Repositories;

use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\SubmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubmissionRepository implements SubmissionRepositoryInterface
{
    public function create(array $data): Submission
    {
        return Submission::create($data);
    }

    public function update(Submission $submission, array $data): Submission
    {
        $submission->update($data);
        return $submission->fresh();
    }

    public function find(string $id): ?Submission
    {
        return Submission::with(['files', 'approvalLogs.approver', 'currentRole', 'requestor'])
            ->find($id);
    }

    public function findByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['currentRole', 'files'])
            ->where('requestor_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function findByCurrentRole(string $roleId): LengthAwarePaginator
    {
        return Submission::with(['requestor', 'files'])
            ->where('current_role_id', $roleId)
            ->whereNotIn('status', ['draft', 'approved', 'rejected'])
            ->latest()
            ->paginate(15);
    }

    public function getDraftsByRequestor(User $user): LengthAwarePaginator
    {
        return Submission::with(['files'])
            ->where('requestor_id', $user->id)
            ->where('status', 'draft')
            ->latest()
            ->paginate(15);
    }
}
```

- [ ] **Step 4: Create ApprovalRepository**

```php
<?php

namespace App\Repositories;

use App\Models\ApprovalLog;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Repositories\Contracts\ApprovalRepositoryInterface;

class ApprovalRepository implements ApprovalRepositoryInterface
{
    public function approve(Submission $submission, User $approver, ?string $notes): Submission
    {
        // Log the approval
        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'approve',
            'notes' => $notes,
        ]);

        // Move to next role
        $currentRole = $submission->currentRole;
        $nextRole = $currentRole->nextRole;

        if ($nextRole === null) {
            // Final approval
            $submission->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        } else {
            $submission->update([
                'current_role_id' => $nextRole->id,
                'status' => $this->getStatusForRole($nextRole),
            ]);
        }

        return $submission->fresh();
    }

    public function reject(Submission $submission, User $approver, string $notes): Submission
    {
        // Log the rejection
        ApprovalLog::create([
            'submission_id' => $submission->id,
            'approver_id' => $approver->id,
            'action' => 'reject',
            'notes' => $notes,
        ]);

        // Get first approver role (SPV Gudang)
        $firstApproverRole = Role::where('name', 'SPV Gudang')->first();

        // Reset to draft
        $submission->update([
            'status' => 'draft',
            'current_role_id' => $firstApproverRole->id,
            'rejected_by' => $approver->id,
            'rejection_reason' => $notes,
        ]);

        return $submission->fresh();
    }

    public function getApprovalHistory(Submission $submission)
    {
        return $submission->approvalLogs()
            ->with('approver.role')
            ->latest()
            ->get();
    }

    private function getStatusForRole(Role $role): string
    {
        return match ($role->name) {
            'SPV Gudang' => 'pending_spv',
            'Kepala Gudang' => 'pending_kepala',
            'Manager Operasional' => 'pending_manager_ops',
            'Direktur Operasional' => 'pending_direktur_ops',
            'Direktur Keuangan' => 'pending_direktur_keuangan',
            default => 'pending',
        };
    }
}
```

- [ ] **Step 5: Register in AppServiceProvider**

```php
<?php

namespace App\Providers;

use App\Repositories\Contracts\SubmissionRepositoryInterface;
use App\Repositories\Contracts\ApprovalRepositoryInterface;
use App\Repositories\SubmissionRepository;
use App\Repositories\ApprovalRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubmissionRepositoryInterface::class, SubmissionRepository::class);
        $this->app->bind(ApprovalRepositoryInterface::class, ApprovalRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add app/Repositories/ app/Providers/
git commit -m "feat(repositories): implement repository pattern for submissions and approvals"
```

- [ ] **Step 7: Merge to development**

```bash
git checkout development
git merge feature/repositories -m "merge: feature/repositories into development"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 4 Verification (Migrations)

- [ ] 5 migration files exist in `database/migrations/`
- [ ] `roles` table has `id` (uuid), `name`, `next_role_id` (uuid, nullable), timestamps
- [ ] `users` table has `id` (uuid), `role_id` (FK), `email` (unique), `password`, `google2fa_secret`, `google2fa_enabled`, timestamps
- [ ] `submissions` table has `id` (uuid), `requestor_id` (FK), `current_role_id` (FK), `status`, `warehouse_name`, `warehouse_address`, `latitude`, `longitude`, `budget_estimate`, `description`, `rejected_by` (FK), `rejection_reason`, `submitted_at`, `approved_at`, timestamps
- [ ] `submission_files` table has `id` (uuid), `submission_id` (FK cascade), `file_name`, `file_path`, `file_size`, `file_type`, timestamps
- [ ] `approval_logs` table has `id` (uuid), `submission_id` (FK cascade), `approver_id` (FK), `action`, `notes`, timestamps
- [ ] All foreign keys have proper constraints (`onDelete('set null')` or `cascadeOnDelete()`)
- [ ] `php artisan migrate:status` shows all migrations completed

```bash
# Verification commands
php artisan migrate:status | grep -q "✓" && echo "✓ all migrations ran"
php artisan db:table roles --json | grep -q '"uuid"' && echo "✓ roles.id is uuid"
php artisan db:table roles --json | grep -q '"next_role_id"' && echo "✓ roles.next_role_id exists"
```

### Task 5 Verification (Models)

- [ ] `app/Models/Role.php` exists with:
  - `$incrementing = false`, `$keyType = 'string'`
  - `$fillable = ['name', 'next_role_id']`
  - `users()`, `nextRole()`, `submissionsAtThisLevel()` relationships
  - `isFinalApprover()` method
- [ ] `app/Models/User.php` exists with:
  - Extends `Authenticatable`, uses `HasFactory, Notifiable`
  - `$incrementing = false`, `$keyType = 'string'`
  - `$fillable` includes `role_id`, `email`, `password`, `google2fa_secret`, `google2fa_enabled`
  - `$hidden` includes `password`, `google2fa_secret`
  - `get2faSecretAttribute()` decrypts secret
  - `set2faSecretAttribute()` encrypts secret
  - `role()`, `submissions()`, `approvalLogs()` relationships
- [ ] `app/Models/Submission.php` exists with:
  - `requestor()`, `currentRole()`, `rejectedBy()`, `files()`, `approvalLogs()` relationships
  - `isPending()`, `isDraft()`, `isApproved()`, `isRejected()` helper methods
  - Proper casts for decimal and datetime fields
- [ ] `app/Models/SubmissionFile.php` exists with `submission()` relationship, `isPdf()`, `isImage()` methods
- [ ] `app/Models/ApprovalLog.php` exists with `submission()`, `approver()` relationships, `isApproval()`, `isRejection()` methods

```bash
# Verification commands
test -f app/Models/Role.php && grep -q "nextRole" app/Models/Role.php && echo "✓ Role model has nextRole"
test -f app/Models/User.php && grep -q "get2faSecretAttribute" app/Models/User.php && echo "✓ User model encrypts 2FA secret"
test -f app/Models/Submission.php && grep -q "isPending" app/Models/Submission.php && echo "✓ Submission has status helpers"
```

### Task 6 Verification (Seeders)

- [ ] `database/seeders/RolesSeeder.php` creates 6 roles in correct order (Requestor → SPV → Kepala → Manager → Direktur Ops → Direktur Keuangan)
- [ ] `database/seeders/UsersSeeder.php` creates 6 users with hardcoded 2FA secrets
- [ ] `database/seeders/DatabaseSeeder.php` calls `RolesSeeder` and `UsersSeeder`
- [ ] Running `php artisan db:seed` creates exactly 6 roles and 6 users
- [ ] 2FA secrets are displayed in console output
- [ ] Secrets are encrypted in database (not stored as plaintext)

```bash
# Verification commands
php artisan db:seed && echo "✓ seeders executed"
psql -d warehouse_approval -c "SELECT COUNT(*) FROM roles;" | grep -q "6" && echo "✓ 6 roles seeded"
psql -d warehouse_approval -c "SELECT COUNT(*) FROM users;" | grep -q "6" && echo "✓ 6 users seeded"
psql -d warehouse_approval -c "SELECT google2fa_secret FROM users LIMIT 1;" | grep -q "eyJpdiI" && echo "✓ secrets are encrypted"
```

### Task 7 Verification (Repositories)

- [ ] `app/Repositories/Contracts/SubmissionRepositoryInterface.php` defines: `create()`, `update()`, `find()`, `findByRequestor()`, `findByCurrentRole()`, `getDraftsByRequestor()`
- [ ] `app/Repositories/Contracts/ApprovalRepositoryInterface.php` defines: `approve()`, `reject()`, `getApprovalHistory()`
- [ ] `app/Repositories/SubmissionRepository.php` implements interface with Eloquent queries
- [ ] `app/Repositories/ApprovalRepository.php` implements interface:
  - `approve()` logs approval, moves to next role or sets status to 'approved'
  - `reject()` logs rejection, resets to 'draft' with SPV as current role
- [ ] `app/Providers/AppServiceProvider.php` binds interfaces to implementations
- [ ] Repository methods eager-load relationships (`with()`)

```bash
# Verification commands
grep -q "SubmissionRepositoryInterface::class" app/Providers/AppServiceProvider.php && echo "✓ repositories bound in container"
grep -q "ApprovalRepository::class" app/Providers/AppServiceProvider.php && echo "✓ ApprovalRepository bound"
test -f app/Repositories/SubmissionRepository.php && grep -q "implements SubmissionRepositoryInterface" app/Repositories/SubmissionRepository.php && echo "✓ SubmissionRepository implements interface"
```

### Branch State Verification

- [ ] Current branch is `development`
- [ ] Feature branches merged: `feature/db-migrations`, `feature/models`, `feature/seeders`, `feature/repositories`
- [ ] No uncommitted changes

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git log --oneline --grep="feat(db)" | head -1 | grep -q "migrations" && echo "✓ migrations committed"
git log --oneline --grep="feat(models)" | head -1 && echo "✓ models committed"
git log --oneline --grep="feat(seeders)" | head -1 && echo "✓ seeders committed"
git log --oneline --grep="feat(repositories)" | head -1 && echo "✓ repositories committed"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

---

**Next Phase:** [phase-2-services.md](phase-2-services.md)
