# Warehouse Approval System - CLAUDE.md

## Project Overview

Build a Laravel + PostgreSQL warehouse construction approval system with 6-level approval workflow, 2FA authentication, geolocation, and document uploads.

**Goal:** Create a production-ready fullstack app with Service-Repository pattern, Inertia.js + React frontend, UUID-based security, and comprehensive test coverage.

**Time Budget:** ~3 hours effective work

---

## Tech Stack

- **Backend:** Laravel 12, PHP 8.3, PostgreSQL 15
- **Frontend:** React 19, Inertia.js, Tailwind CSS 4, TypeScript
- **Package Manager:** Bun 1.0+
- **2FA:** pragmarx/google2fa-laravel, bacon/bacon-qr-code
- **Testing:** PHPUnit (backend), Vitest (frontend)
- **Maps:** Leaflet.js + OpenStreetMap

---

## Project Structure

```
/srv/sfpd-works/sfpd-test/
│
├── composer.json                # PHP dependencies
├── package.json                 # JS dependencies
├── phpunit.xml                  # PHPUnit config
├── vite.config.js               # Vite config (includes @tailwindcss/vite)
├── tsconfig.json                # TypeScript config
├── .env.example
├── .gitignore
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   └── TwoFactorController.php
│   │   │   ├── Submissions/
│   │   │   │   └── SubmissionController.php
│   │   │   ├── Approvals/
│   │   │   │   └── ApprovalController.php
│   │   │   └── DashboardController.php
│   │   ├── Middleware/
│   │   │   ├── Ensure2FAEnabled.php
│   │   │   └── RoleCheck.php
│   │   └── Requests/
│   │       ├── LoginRequest.php
│   │       ├── TwoFactorRequest.php
│   │       ├── StoreSubmissionRequest.php
│   │       └── ApproveSubmissionRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Submission.php
│   │   ├── SubmissionFile.php
│   │   └── ApprovalLog.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── SubmissionRepositoryInterface.php
│   │   │   └── ApprovalRepositoryInterface.php
│   │   ├── SubmissionRepository.php
│   │   └── ApprovalRepository.php
│   ├── Services/
│   │   ├── SubmissionService.php
│   │   ├── ApprovalWorkflowService.php
│   │   └── TwoFactorAuthService.php
│   └── Providers/
│       └── AppServiceProvider.php
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000001_create_roles_table.php
│   │   ├── 0001_01_01_000002_create_users_table.php
│   │   ├── 0001_01_01_000003_create_submissions_table.php
│   │   ├── 0001_01_01_000004_create_submission_files_table.php
│   │   └── 0001_01_01_000005_create_approval_logs_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── RolesSeeder.php
│   │   └── UsersSeeder.php
│   └── factories/
│       ├── UserFactory.php
│       └── SubmissionFactory.php
│
├── resources/
│   ├── js/
│   │   ├── app.tsx
│   │   ├── bootstrap.tsx
│   │   ├── Pages/
│   │   │   ├── Auth/
│   │   │   │   ├── Login.tsx
│   │   │   │   └── TwoFactorVerify.tsx
│   │   │   ├── Dashboard/
│   │   │   │   └── Dashboard.tsx
│   │   │   └── Submissions/
│   │   │       ├── Index.tsx
│   │   │       ├── Create.tsx
│   │   │       └── Show.tsx
│   │   ├── Components/
│   │   │   ├── Layouts/
│   │   │   │   └── AppLayout.tsx
│   │   │   ├── UI/
│   │   │   │   ├── StatusBadge.tsx
│   │   │   │   └── FileUpload.tsx
│   │   │   └── Maps/
│   │   │       └── LocationMap.tsx
│   │   ├── hooks/
│   │   │   └── useFormValidation.ts
│   │   └── lib/
│   │       └── utils.ts
│   └── css/
│       └── app.css
│
├── routes/
│   ├── web.php
│   └── auth.php
│
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── SubmissionServiceTest.php
│   │   │   ├── ApprovalWorkflowServiceTest.php
│   │   │   └── TwoFactorAuthServiceTest.php
│   │   └── Repositories/
│   │       └── SubmissionRepositoryTest.php
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   └── TwoFactorTest.php
│   │   ├── Submissions/
│   │   │   ├── CreateSubmissionTest.php
│   │   │   ├── ApprovalWorkflowTest.php
│   │   │   └── DocumentUploadTest.php
│   │   └── RoleAccessTest.php
│   └── TestCase.php
│
├── docs/
│   ├── ASSUMPTIONS.md           # Key assumptions
│   ├── IMPROVEMENTS.md          # Future enhancements
│   ├── plans/
│   │   └── 2026-04-21-warehouse-approval.md
│   └── specs/
│       ├── phase-0-setup.md
│       ├── phase-1-backend-core.md
│       ├── phase-2-services.md
│       ├── phase-3-auth-controllers.md
│       ├── phase-4-validation-routes.md
│       ├── phase-5-testing.md
│       ├── phase-6-frontend.md
│       └── phase-7-docs.md
│
└── README.md
```

---

## Core Rules

### 1. Always Use Sequential Thinking

For any complex task or decision, use `mcp__sequential-thinking__sequentialthinking` to work through problems methodically.

### 2. Always Consult Context7 for Documentation

Before implementing any library feature:

- Use `mcp__plugin_context7_context7__query-docs` to fetch current documentation
- Verify API syntax, configuration, and best practices
- Libraries: Laravel, Inertia.js, React, Tailwind, google2fa-laravel

### 3. Service-Repository Pattern

- **Controllers:** Handle HTTP requests, validation, responses
- **Services:** Business logic, workflow orchestration, transactions
- **Repositories:** Data access, queries, eager-loading
- **Models:** Eloquent ORM, relationships, accessors/mutators

### 4. TDD Approach

- Write failing tests first
- Implement minimal code to pass
- Refactor with confidence
- Run tests frequently

### 5. Frequent Commits

- Commit after each passing test
- Commit after each feature complete
- Use conventional commits: `feat(scope): description`
- Commit format: `<type>(<scope>): <description>`
  - Types: feat, fix, docs, style, refactor, test, chore, init
  - Scopes: auth, submissions, approvals, users, db, ui, config, tests, project

### 6. Git Flow

- `main` - Production-ready code
- `development` - Integration branch
- `feature/*` - Individual features branched from `development`

---

## Architecture

### Approval Workflow

```
Requestor → SPV Gudang → Kepala Gudang → Manager Operasional → Direktur Operasional → Direktur Keuangan
```

- **Rejection:** Returns to `draft` status, restarts from SPV level
- **next_role_id:** Self-referencing FK on `roles` table drives workflow chain

### Key Design Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| ID Type | UUID | IDOR protection, unguessable |
| Architecture | Service-Repository | Clean separation, testable |
| Frontend | Inertia.js + React | Modern SPA without API complexity |
| Package Manager | Bun | Faster installs |
| Map Library | Leaflet.js + OpenStreetMap | Free, no API key |
| 2FA Library | pragmarx/google2fa-laravel | Laravel integration, QR support |

---

## Definition of Done

### Phase 0: Setup

- [ ] Git setup with husky + commitlint
- [ ] All dependencies installed (PHP + JS)
- [ ] Initial commit on `main` with `v0.1.0` tag
- [ ] `development` branch created

### Phase 1: Backend Core

- [ ] 5 migrations created and run (roles, users, submissions, submission_files, approval_logs)
- [ ] 5 models with relationships and helper methods
- [ ] Seeders create 6 roles + 6 users with encrypted 2FA secrets
- [ ] Repositories implement interfaces with proper queries

### Phase 2: Backend Services

- [ ] SubmissionService with CRUD operations
- [ ] ApprovalWorkflowService with approve/reject logic
- [ ] TwoFactorAuthService with TOTP generation/verification

### Phase 3: Auth & Controllers

- [ ] Middleware (Ensure2FAEnabled, RoleCheck) registered
- [ ] LoginController with 2FA redirect logic
- [ ] TwoFactorController with code verification
- [ ] SubmissionController with CRUD methods
- [ ] ApprovalController with approve/reject actions

### Phase 4: Validation & Routes

- [ ] Form requests for all inputs
- [ ] Routes defined for auth, submissions, approvals
- [ ] All routes have named route definitions

### Phase 5: Testing

- [ ] Unit tests for services and repositories
- [ ] Feature tests for auth flow, submission creation, approval workflow
- [ ] All tests passing with `php artisan test`

### Phase 6: Frontend

- [ ] AppLayout with navigation and logout
- [ ] StatusBadge component for all statuses
- [ ] Auth pages (Login, TwoFactorVerify)
- [ ] Submission pages (Index, Create, Show)
- [ ] Dashboard with pending approvals and my submissions
- [ ] `bun run build` completes without errors

### Phase 7: Documentation

- [ ] README.md with setup, 2FA secrets table, workflow diagram
- [ ] ASSUMPTIONS.md documents key decisions
- [ ] IMPROVEMENTS.md lists future enhancements
- [ ] Merged to `main` with `v0.2.0` tag

---

## Commands

```bash
# Root
bun install                    # Install JS dependencies
composer install               # Install PHP dependencies

# Setup
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# Development
bun run dev                    # Vite dev server
php artisan serve              # Laravel dev server (port 8000)

# Testing
php artisan test               # Run PHPUnit tests
php artisan test --coverage    # With coverage

# Build
bun run build                  # Production build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Git
git status                     # Check status
git add . && git commit -m "feat(scope): description"
git checkout -b feature/xyz    # Create feature branch
git merge feature/xyz          # Merge feature branch
```

---

## 2FA Test Credentials

All users have password: `password123`

| Role | Email | 2FA Secret |
|------|-------|------------|
| Requestor | requestor@test.com | JBSWY3DPEHPK3PXP |
| SPV Gudang | spv@test.com | KRSXG5CTMVRXEZLU |
| Kepala Gudang | kepala@test.com | GEZDGNBVGY3TQOJQ |
| Manager Operasional | manager@test.com | MFRGGZDFMY2TQNZZ |
| Direktur Operasional | direktur.ops@test.com | OVSG433SMVZWKZTH |
| Direktur Keuangan | direktur.keuangan@test.com | KRSXG5CTMVRXEZTB |

---

## Implementation Plan

See detailed plan: [docs/plans/2026-04-21-warehouse-approval.md](docs/plans/2026-04-21-warehouse-approval.md)

Detailed specs for each phase:
- [Phase 0: Setup](docs/specs/phase-0-setup.md) - Git, dependencies, initial commit
- [Phase 1: Backend Core](docs/specs/phase-1-backend-core.md) - Migrations, models, seeders, repositories
- [Phase 2: Services](docs/specs/phase-2-services.md) - SubmissionService, ApprovalWorkflowService, TwoFactorAuthService
- [Phase 3: Auth & Controllers](docs/specs/phase-3-auth-controllers.md) - Middleware, controllers
- [Phase 4: Validation & Routes](docs/specs/phase-4-validation-routes.md) - Form requests, routes
- [Phase 5: Testing](docs/specs/phase-5-testing.md) - Unit and feature tests
- [Phase 6: Frontend](docs/specs/phase-6-frontend.md) - React components and pages
- [Phase 7: Documentation](docs/specs/phase-7-docs.md) - README and docs

**Execution approach:** Use `superpowers:subagent-driven-development` for parallel task execution.
