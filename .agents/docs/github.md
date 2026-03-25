# Git Workflow Guide

## ⚠️ CRITICAL: Local Only Workflow

**DO NOT PUSH, MERGE, OR DELETE BRANCHES**

This workflow is for **local commits only**. Never execute:
- `git push` - Do not push to remote
- `git merge` - Do not merge branches
- `git pull` - Do not pull from remote
- `git branch -d` - Do not delete branches
- `git rebase` - Do not rebase

All git operations should remain local for tracking and organizing your work.

## Before Committing Changes

### 1. Analyze Changes
Before staging any files, always review what has changed in your codebase:

```bash
git status
```

This shows:
- Modified files
- New untracked files
- Deleted files
- Current branch

### 2. Review Detailed Changes
Examine the actual code changes:

```bash
git diff
```

For staged changes:
```bash
git diff --cached
```

### 3. Assess the Changes
Ask yourself:
- What type of change is this? (feature, fix, refactor, docs, etc.)
- Are all changes related to a single logical unit?
- Should any files be excluded or committed separately?
- Do the changes align with the project architecture?

### 4. Stage All Changes
Once you've reviewed and assessed, stage everything at once:

```bash
git add .
```

**Always use `git add .` to stage all changes in one command.**

## Commit Message Convention

Use **Conventional Commits** format for clear, semantic commit history:

### Format
```
<type>: <description>

[optional body]

[optional footer]
```

### Types

- **feat**: New feature or functionality
  ```bash
  git commit -m "feat: add Maya payment webhook handler"
  git commit -m "feat: implement HighLevel payment creation service"
  ```

- **fix**: Bug fix
  ```bash
  git commit -m "fix: resolve payment status mapping issue"
  git commit -m "fix: handle null response in Maya API client"
  ```

- **refactor**: Code restructuring without changing behavior
  ```bash
  git commit -m "refactor: extract payment logic into service layer"
  git commit -m "refactor: simplify DTO transformation logic"
  ```

- **docs**: Documentation changes
  ```bash
  git commit -m "docs: update API integration guide"
  git commit -m "docs: add payment flow diagram"
  ```

- **test**: Adding or updating tests
  ```bash
  git commit -m "test: add unit tests for PaymentService"
  git commit -m "test: add integration tests for webhook endpoints"
  ```

- **chore**: Maintenance tasks, dependencies, config
  ```bash
  git commit -m "chore: update composer dependencies"
  git commit -m "chore: configure environment variables"
  ```

- **style**: Code style/formatting changes
  ```bash
  git commit -m "style: format code according to PSR-12"
  ```

- **perf**: Performance improvements
  ```bash
  git commit -m "perf: optimize database queries in payment lookup"
  ```

### Scope (Optional)
Add scope for more context:
```bash
git commit -m "feat(webhook): add Maya payment status handler"
git commit -m "fix(api): correct HighLevel authentication flow"
git commit -m "test(service): add PaymentService unit tests"
```

### Breaking Changes
For breaking changes, add `!` or `BREAKING CHANGE:` in footer:
```bash
git commit -m "feat!: change payment DTO structure"
```

## Complete Workflow Example

```bash
# 1. Check what changed
git status

# 2. Review the changes
git diff

# 3. Assess: "I added a new payment webhook endpoint"
# Type: feat (new feature)

# 4. Stage changes
git add .

# 5. Commit with conventional message
git commit -m "feat: add Maya payment webhook endpoint"

# 6. DO NOT PUSH - Keep commits local only
```

## Multi-Change Commits

If changes span multiple types, assess which type is dominant and use that:

```bash
# Stage all changes at once
git add .

# Commit with the primary change type
git commit -m "feat: implement payment processing service with tests and docs"
```

## Quick Reference

| Change Type | Commit Prefix |
|-------------|---------------|
| New feature | `feat:` |
| Bug fix | `fix:` |
| Refactoring | `refactor:` |
| Documentation | `docs:` |
| Tests | `test:` |
| Maintenance | `chore:` |
| Styling | `style:` |
| Performance | `perf:` |
