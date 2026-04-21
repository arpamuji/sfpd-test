# Phase 7: Documentation - Detailed Specification

**Task:** 20  
**Branch:** `development` → `main`

---

## Task 20: README & Documentation

**Files:**
- Create: `README.md`
- Modify: `docs/ASSUMPTIONS.md` (already exists)
- Modify: `docs/IMPROVEMENTS.md` (already exists)

### Steps

- [ ] **Step 1: Create README.md**

```markdown
# Warehouse Construction Approval System

Laravel-based warehouse construction approval system with 6-level approval workflow, 2FA authentication, geolocation, and document uploads.

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3
- **Frontend:** React 19, Inertia.js, Tailwind CSS 4
- **Database:** PostgreSQL 15
- **Package Manager:** Bun
- **2FA:** pragmarx/google2fa-laravel

## Requirements

- PHP 8.3+
- PostgreSQL 15+
- Bun 1.0+
- Composer

## Setup

### 1. Clone and Install

```bash
git clone <repository-url>
cd sfpd-test
bun install
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=warehouse_approval
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 4. Run Development Servers

Terminal 1 (Laravel):
```bash
php artisan serve
```

Terminal 2 (Vite):
```bash
bun run dev
```

Access at: http://localhost:8000

## Testing

### 2FA Login Credentials

All test users have password: `password123`

| Role | Email | 2FA Secret |
|------|-------|------------|
| Requestor | requestor@test.com | `JBSWY3DPEHPK3PXP` |
| SPV Gudang | spv@test.com | `KRSXG5CTMVRXEZLU` |
| Kepala Gudang | kepala@test.com | `GEZDGNBVGY3TQOJQ` |
| Manager Operasional | manager@test.com | `MFRGGZDFMY2TQNZZ` |
| Direktur Operasional | direktur.ops@test.com | `OVSG433SMVZWKZTH` |
| Direktur Keuangan | direktur.keuangan@test.com | `KRSXG5CTMVRXEZTB` |

### Setting Up Google Authenticator

1. Install Google Authenticator app on your phone
2. During first login, you'll be shown a QR code
3. Alternatively, manually enter the secret from the table above
4. Enter the 6-digit code from the app

### Running Tests

```bash
php artisan test
```

## Approval Workflow

```
Requestor → SPV Gudang → Kepala Gudang → Manager Operasional → Direktur Operasional → Direktur Keuangan
```

1. **Requestor** creates submission with:
   - Warehouse name and address
   - Geolocation (latitude/longitude)
   - Budget estimate
   - Minimum 3 PDF documents + optional images

2. **SPV Gudang** reviews and approves/rejects

3. **Kepala Gudang** reviews and approves/rejects

4. **Manager Operasional** reviews and approves/rejects

5. **Direktur Operasional** reviews and approves/rejects

6. **Direktur Keuangan** final approval

**Rejection:** Returns submission to `draft` status, requiring resubmission from SPV level.

## Architecture

### Service-Repository Pattern

```
Controllers → Services → Repositories → Models
```

- **Controllers:** Handle HTTP requests, validation, responses
- **Services:** Business logic, workflow orchestration
- **Repositories:** Data access, queries
- **Models:** Eloquent ORM, relationships

### Key Design Decisions

- **UUID Primary Keys:** Prevent IDOR vulnerabilities
- **next_role_id Chain:** Database-driven approval workflow
- **Encrypted 2FA Secrets:** AES-256-CBC encryption at rest
- **Inertia.js:** SPA experience without API complexity

## Project Structure

```
app/
├── Http/
│   ├── Controllers/    # Request handling
│   ├── Middleware/     # 2FA, Role checks
│   └── Requests/       # Form validation
├── Models/             # Eloquent models
├── Repositories/       # Data access layer
└── Services/           # Business logic
database/
├── migrations/         # Database schema
└── seeders/            # Test data
resources/js/
├── Components/         # React components
└── Pages/              # Inertia pages
```

## Documentation

- [Assumptions](docs/ASSUMPTIONS.md) - Key assumptions and design decisions
- [Improvements](docs/IMPROVEMENTS.md) - Future enhancements

## License

Proprietary - PT Safepedia Global Teknologi
```

- [ ] **Step 2: Update ASSUMPTIONS.md** (already exists, verify content)

Ensure these points are covered:
- Static 6-level approval workflow
- One user = one role
- Rejection returns to draft
- Minimum 3 PDF + optional images
- 2FA secrets encrypted at rest
- Hardcoded secrets for testing
- No public registration

- [ ] **Step 3: Update IMPROVEMENTS.md** (already exists, verify content)

Ensure these are documented:
- Dynamic workflow engine
- Status lookup table
- ULID over UUID
- Soft deletes
- Event-driven notifications
- Multi-role support
- Email notifications
- PDF export

- [ ] **Step 4: Commit documentation**

```bash
git add README.md docs/
git commit -m "docs(project): add README, assumptions, and improvements documentation"
```

- [ ] **Step 5: Merge to main**

```bash
git checkout main
git merge development -m "merge: development into main for v0.2.0"
git tag -a v0.2.0 -m "Complete warehouse approval system"
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 20 Verification (README)

- [ ] `README.md` exists at project root
- [ ] README contains project title and description
- [ ] README contains Tech Stack section listing Laravel 12, React 19, PostgreSQL, Bun
- [ ] README contains Requirements section
- [ ] README contains Setup section with:
  - Clone and install steps (`bun install`, `composer install`)
  - Environment configuration (`.env.example` copy, `APP_KEY` generation)
  - Database setup commands (`migrate`, `db:seed`)
  - Development server commands (`php artisan serve`, `bun run dev`)
- [ ] README contains Testing section with:
  - 2FA Login Credentials table with all 6 users, emails, and secrets
  - Google Authenticator setup instructions
  - `php artisan test` command
- [ ] README contains Approval Workflow diagram (Requestor → SPV → Kepala → Manager → Direktur Ops → Direktur Keuangan)
- [ ] README contains Architecture section describing Service-Repository pattern
- [ ] README contains Project Structure tree
- [ ] README contains links to ASSUMPTIONS.md and IMPROVEMENTS.md

```bash
# Verification commands
test -f README.md && echo "✓ README.md exists"
grep -q "Tech Stack" README.md && echo "✓ README has Tech Stack section"
grep -q "password123" README.md && echo "✓ README has default password"
grep -q "JBSWY3DPEHPK3PXP" README.md && echo "✓ README has 2FA secrets table"
grep -q "Approval Workflow" README.md && echo "✓ README has workflow section"
grep -q "Service-Repository" README.md && echo "✓ README has architecture section"
grep -q "ASSUMPTIONS.md" README.md && echo "✓ README links to assumptions"
grep -q "IMPROVEMENTS.md" README.md && echo "✓ README links to improvements"
```

### Documentation Files Verification

- [ ] `docs/ASSUMPTIONS.md` exists with:
  - Static 6-level approval workflow documented
  - One user = one role assumption
  - Rejection returns to draft assumption
  - Minimum 3 PDF + optional images requirement
  - 2FA secrets encrypted at rest
  - Hardcoded secrets for testing
  - No public registration
- [ ] `docs/IMPROVEMENTS.md` exists with:
  - Dynamic workflow engine suggestion
  - Status lookup table suggestion
  - ULID over UUID consideration
  - Soft deletes suggestion
  - Event-driven notifications suggestion
  - Multi-role support suggestion
  - Email notifications suggestion
  - PDF export suggestion

```bash
# Verification commands
test -f docs/ASSUMPTIONS.md && echo "✓ ASSUMPTIONS.md exists"
test -f docs/IMPROVEMENTS.md && echo "✓ IMPROVEMENTS.md exists"
grep -q "static" docs/ASSUMPTIONS.md && echo "✓ ASSUMPTIONS mentions static workflow"
grep -q "encrypted" docs/ASSUMPTIONS.md && echo "✓ ASSUMPTIONS mentions encryption"
grep -q "Dynamic workflow" docs/IMPROVEMENTS.md && echo "✓ IMPROVEMENTS has dynamic workflow"
grep -q "Email" docs/IMPROVEMENTS.md && echo "✓ IMPROVEMENTS has email notifications"
```

### Final Merge Verification

- [ ] All changes merged to `main` branch
- [ ] `v0.2.0` tag created on `main`
- [ ] `main` branch contains all features:
  - Migrations, models, seeders
  - Repositories, services
  - Controllers, middleware, form requests
  - Routes
  - Tests
  - Frontend components and pages
  - Documentation

```bash
# Verification commands
git checkout main && echo "✓ switched to main"
git tag -l | grep -q "v0.2.0" && echo "✓ v0.2.0 tag exists"
git log --oneline --grep="merge.*development" | head -1 && echo "✓ development merged to main"
```

### Full Application Verification

- [ ] `php artisan migrate:status` shows all migrations completed
- [ ] `php artisan db:seed` runs without errors
- [ ] `php artisan route:list` shows all routes registered
- [ ] `php artisan test` passes all tests
- [ ] `bun run build` completes without errors
- [ ] Application is accessible at http://localhost:8000
- [ ] Login flow works with seeded users and 2FA secrets

```bash
# Verification commands
php artisan migrate:status 2>&1 | grep -q "✓" && echo "✓ migrations complete"
php artisan db:seed && echo "✓ seeders run"
php artisan test 2>&1 | grep -q "OK" && echo "✓ all tests passing"
bun run build 2>&1 | grep -q "built in" && echo "✓ frontend builds"
```

---

## Implementation Complete

All 20 tasks completed. The system includes:

- 6-level approval workflow with `next_role_id` chain
- TOTP 2FA authentication with encrypted secrets
- Service-Repository pattern
- Inertia.js + React frontend
- UUID primary keys for security
- Comprehensive test suite
- Full documentation
