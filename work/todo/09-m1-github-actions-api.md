---
title: GitHub Actions — backend lint/test/deploy
id: 09
priority: medium
status: todo
milestone: 1
module: A-core
area: devops
effort: L
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# GitHub Actions — backend lint/test/deploy

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-09

## What to do (What)
PR: lint/test backend. Main: GitHub build → SSH release → migrate → reload → health → rollback. Secrets in GitHub only.

## Current / Before
No workflows.

## Expected / After
API CI/CD workflows + docs.

## Why
PDF §6 CI/CD; Milestone 1 auto deploy.

## Acceptance Criteria (complete only when all ticked)
- [ ] PR lint/test workflow
- [ ] Main deploy path (implemented or fully documented pending SSH)
- [ ] Health check + rollback behavior
- [ ] No secrets in repo
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `.github/workflows/**`
- deploy docs
