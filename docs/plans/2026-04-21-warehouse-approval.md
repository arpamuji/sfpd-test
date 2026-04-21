# Warehouse Approval System - High-Level Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` to implement this plan task-by-task.

**Goal:** Build a Laravel + PostgreSQL warehouse construction approval system with 6-level approval workflow, 2FA authentication, geolocation, and document uploads.

**Architecture:** Service-Repository pattern with Inertia.js + React frontend, UUID-based IDs for IDOR protection, workflow driven by `next_role_id` chain.

**Tech Stack:** Laravel 12, Inertia.js + React 19, Tailwind CSS 4, PostgreSQL, pragmarx/google2fa-laravel, Leaflet.js, Bun, PHPUnit, Vitest.

---

## Phase Overview

| Phase | Tasks | Spec File |
|-------|-------|-----------|
| **Phase 0: Project Setup** | 1-3 | [specs/phase-0-setup.md](../specs/phase-0-setup.md) |
| **Phase 1: Backend Core** | 4-7 | [specs/phase-1-backend-core.md](../specs/phase-1-backend-core.md) |
| **Phase 2: Backend Services** | 8-10 | [specs/phase-2-services.md](../specs/phase-2-services.md) |
| **Phase 3: Auth & Controllers** | 11-13 | [specs/phase-3-auth-controllers.md](../specs/phase-3-auth-controllers.md) |
| **Phase 4: Validation & Routes** | 14-15 | [specs/phase-4-validation-routes.md](../specs/phase-4-validation-routes.md) |
| **Phase 5: Testing** | 16 | [specs/phase-5-testing.md](../specs/phase-5-testing.md) |
| **Phase 6: Frontend** | 17-19 | [specs/phase-6-frontend.md](../specs/phase-6-frontend.md) |
| **Phase 7: Documentation** | 20 | [specs/phase-7-docs.md](../specs/phase-7-docs.md) |

---

## Task Summary

### Phase 0: Project Setup (Tasks 1-3)
- **Task 1:** Git Setup - Husky & Commitlint
- **Task 2:** Install Dependencies (PHP + JS)
- **Task 3:** Initial Commit to Main + Create Development Branch

### Phase 1: Backend Core (Tasks 4-7)
- **Task 4:** Database Migrations (5 tables with UUID + FKs)
- **Task 5:** Eloquent Models (User, Role, Submission, SubmissionFile, ApprovalLog)
- **Task 6:** Seeders (6 roles, 6 users with hardcoded 2FA secrets)
- **Task 7:** Repositories (SubmissionRepository, ApprovalRepository + interfaces)

### Phase 2: Backend Services (Tasks 8-10)
- **Task 8:** SubmissionService (CRUD operations)
- **Task 9:** ApprovalWorkflowService (approve/reject with workflow progression)
- **Task 10:** TwoFactorAuthService (TOTP generation, verification, QR codes)

### Phase 3: Auth & Controllers (Tasks 11-13)
- **Task 11:** Middleware (Ensure2FAEnabled, RoleCheck)
- **Task 12:** Auth Controllers (LoginController, TwoFactorController)
- **Task 13:** Submission Controllers (SubmissionController, ApprovalController, DashboardController)

### Phase 4: Validation & Routes (Tasks 14-15)
- **Task 14:** Form Requests (LoginRequest, TwoFactorRequest, StoreSubmissionRequest, ApproveSubmissionRequest)
- **Task 15:** Routes (web.php, auth.php)

### Phase 5: Testing (Task 16)
- **Task 16:** Backend Tests (Unit: Services, Repositories; Feature: Auth, Submissions, Role Access)

### Phase 6: Frontend (Tasks 17-19)
- **Task 17:** Layout Components (AppLayout, StatusBadge, FileUpload)
- **Task 18:** Auth Pages (Login, TwoFactorVerify)
- **Task 19:** Submission Pages (Dashboard, Index, Create, Show with LocationMap)

### Phase 7: Documentation (Task 20)
- **Task 20:** README.md with setup instructions, 2FA secrets table, architecture overview

---

## Git Flow

```
main (production-ready)
  │
  └── [INITIAL COMMIT] ──► tagged as v0.1.0
        │
        └── development (integration branch)
              │
              ├── feature/db-migrations
              ├── feature/models
              ├── feature/seeders
              ├── feature/repositories
              ├── feature/services
              ├── feature/middleware
              ├── feature/auth-controllers
              ├── feature/submission-controllers
              ├── feature/validation
              ├── feature/routes
              ├── feature/backend-tests
              └── feature/frontend
```

**Commit Format:** `<type>(<scope>): <description>`

**Types:** feat, fix, docs, style, refactor, test, chore, init  
**Scopes:** auth, submissions, approvals, users, db, ui, config, tests, project

---

## Related Documents

- [ASSUMPTIONS.md](../ASSUMPTIONS.md) - Key assumptions and design decisions
- [IMPROVEMENTS.md](../IMPROVEMENTS.md) - Future enhancements and technical debt
- [Architecture Diagram](#architecture-layers) - System architecture overview

---

## Architecture Layers

```
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND (React + Inertia)             │
│  Pages → Components → hooks → lib                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    CONTROLLERS (HTTP Layer)                 │
│  ↓ Uses Form Requests for validation                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    SERVICES (Business Logic)                │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                 REPOSITORIES (Data Access)                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                   MODELS (Eloquent ORM)                     │
└─────────────────────────────────────────────────────────────┘
```

---

**Start implementation with Phase 0. See [specs/phase-0-setup.md](../specs/phase-0-setup.md) for detailed tasks.**
