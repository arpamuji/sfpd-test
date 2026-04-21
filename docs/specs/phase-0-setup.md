# Phase 0: Project Setup - Detailed Specification

**Tasks:** 1-3  
**Branch:** `main` → `development`

---

## Task 1: Git Setup - Husky & Commitlint

**Branch:** `development`

**Files:**
- Create: `.husky/commit-msg`
- Create: `.commitlintrc.json`
- Create: `.gitmessage`

### Steps

- [ ] **Step 1: Install husky and commitlint**

```bash
bun add -d husky commitlint @commitlint/config-conventional
```

- [ ] **Step 2: Initialize husky**

```bash
bunx husky install
bunx husky add .husky/commit-msg 'bunx --no-install commitlint --edit $1'
```

- [ ] **Step 3: Create commitlint config**

```json
{
  "extends": ["@commitlint/config-conventional"],
  "rules": {
    "type-enum": [2, "always", ["feat", "fix", "docs", "style", "refactor", "test", "chore", "init"]],
    "scope-enum": [2, "always", ["auth", "submissions", "approvals", "users", "db", "ui", "config", "tests", "project"]],
    "subject-max-length": [2, "always", 72]
  }
}
```

- [ ] **Step 4: Create git commit message template**

```bash
cat > .gitmessage << 'EOF'
# <type>(<scope>): <subject>
# |<----  Using a Maximum Of 72 Characters  ---->|
#
# Type: feat, fix, docs, style, refactor, test, chore, init
# Scope: auth, submissions, approvals, users, db, ui, config, tests, project
#
# Example: feat(auth): add 2FA verification endpoint
#
EOF
git config commit.template .gitmessage
```

- [ ] **Step 5: Commit setup**

```bash
git add .husky/ .commitlintrc.json .gitmessage
git commit -m "chore(config): add husky and commitlint for commit standards"
```

---

## Task 2: Install Dependencies

**Branch:** `development`

**Files:**
- Modify: `composer.json`
- Create: `vite.config.js`, `tsconfig.json`, `postcss.config.js`

### Steps

- [ ] **Step 1: Install PHP dependencies**

```bash
composer require pragmarx/google2fa-laravel bacon/bacon-qr-code
```

- [ ] **Step 2: Install JavaScript dependencies**

```bash
bun add @inertiajs/react react react-dom
bun add -d typescript @types/react @types/react-dom vite @vitejs/plugin-react tailwindcss postcss autoprefixer
```

- [ ] **Step 3: Create vite.config.js**

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        react(),
    ],
});
```

- [ ] **Step 4: Install @tailwindcss/vite**

```bash
bun add -d @tailwindcss/vite
```

Note: Tailwind CSS 4 uses `@tailwindcss/vite` plugin instead of `tailwind.config.js`. Configuration is done in `app.css` with `@import 'tailwindcss'`.

- [ ] **Step 5: Create tsconfig.json**

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "module": "ESNext",
    "moduleResolution": "bundler",
    "jsx": "react-jsx",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "forceConsistentCasingInFileNames": true,
    "paths": {
      "@/*": ["./resources/js/*"]
    }
  },
  "include": ["resources/js/**/*"]
}
```

- [ ] **Step 6: Commit**

```bash
git add composer.json vite.config.js tsconfig.json postcss.config.js
git commit -m "chore(deps): add Inertia + React + 2FA dependencies"
```

---

## Task 3: Initial Commit to Main

**Goal:** Create stable initial commit on `main` branch, then create `development` branch.

### Steps

- [ ] **Step 1: Add all project files**

```bash
git add -A
```

- [ ] **Step 2: Create initial commit on main**

```bash
git commit -m "init(project): initial project structure with Laravel 12 + Inertia + React"
```

- [ ] **Step 3: Tag the initial commit**

```bash
git tag -a v0.1.0 -m "Initial project setup"
```

- [ ] **Step 4: Create development branch**

```bash
git checkout -b development
```

---

## Definition of Done

Verify all items below before marking this phase complete.

### Task 1 Verification (Git Setup)

- [ ] `.husky/commit-msg` file exists and is executable
- [ ] `.commitlintrc.json` exists with correct type-enum and scope-enum rules
- [ ] `.gitmessage` exists with commit template format
- [ ] `git config commit.template` returns `.gitmessage`
- [ ] Test commit with invalid message format is rejected by commitlint
- [ ] Test commit with valid format (`feat(auth): test message`) succeeds

```bash
# Verification commands
test -x .husky/commit-msg && echo "✓ husky hook executable"
cat .commitlintrc.json | grep -q '"type-enum"' && echo "✓ type-enum configured"
git config commit.template | grep -q '.gitmessage' && echo "✓ template configured"
```

### Task 2 Verification (Dependencies)

- [ ] `pragmarx/google2fa-laravel` in `composer.json` require section
- [ ] `bacon/bacon-qr-code` in `composer.json` require section
- [ ] `@inertiajs/react`, `react`, `react-dom` in `package.json` dependencies
- [ ] `typescript`, `vite`, `@vitejs/plugin-react`, `@tailwindcss/vite` in `package.json` devDependencies
- [ ] `vite.config.js` exists with laravel and react plugins configured, tailwindcss plugin imported
- [ ] `resources/css/app.css` exists with `@import 'tailwindcss'` directive
- [ ] `tsconfig.json` exists with `jsx: "react-jsx"` and path alias `@/*`
- [ ] `composer.json` has `laravel-vite-plugin` dependency

```bash
# Verification commands
composer show pragmarx/google2fa-laravel | grep -q "versions" && echo "✓ google2fa installed"
bun list --depth=0 | grep -q "@inertiajs/react" && echo "✓ inertia installed"
test -f vite.config.js && grep -q "laravel-vite-plugin" vite.config.js && echo "✓ vite configured"
```

### Task 3 Verification (Initial Commit)

- [ ] Current branch is `development`
- [ ] `git log --oneline` shows initial commit with message starting with `init(project):`
- [ ] `git tag -l` shows `v0.1.0` tag
- [ ] `git branch --show-current` returns `development`
- [ ] No uncommitted changes (`git status` shows clean working tree)

```bash
# Verification commands
git branch --show-current | grep -q "development" && echo "✓ on development branch"
git tag -l | grep -q "v0.1.0" && echo "✓ v0.1.0 tag exists"
git log --oneline -1 | grep -q "init(project)" && echo "✓ initial commit exists"
git status --porcelain | wc -l | grep -q "^0$" && echo "✓ working tree clean"
```

### Branch State Verification

- [ ] `main` branch exists with initial commit
- [ ] `development` branch exists and is current
- [ ] Both branches have same commit history up to initial commit

```bash
# Verification commands
git branch | grep -q "main" && echo "✓ main branch exists"
git branch | grep -q "\* development" && echo "✓ development is current"
```

---

**Next Phase:** [phase-1-backend-core.md](phase-1-backend-core.md)
