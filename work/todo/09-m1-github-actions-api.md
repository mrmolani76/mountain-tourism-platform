---
title: GitHub Actions — backend lint/test/deploy
id: 09
priority: medium
status: todo
milestone: 1
module: infra
area: devops
effort: L
dependencies: 03
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# GitHub Actions — backend lint/test/deploy

## What to do (What)
CI on PR: install, lint, test (backend). On merge to `main`: build on GitHub, SSH release folder on VPS, migrate, switch release, reload, health check, auto-rollback on failure. Secrets in GitHub Secrets only.

## Current / Before
No workflows.

## Expected / After
`.github/workflows/` for API; docs note deploy; last N releases keepable.

## Why
PDF §6 CI/CD; Milestone 1 automatic deploy.

## Acceptance Criteria (complete only when all ticked)
- [ ] PR pipeline runs backend lint/tests
- [ ] Main deploy path documented (and implemented when SSH available)
- [ ] Health check + rollback behavior described/implemented
- [ ] No secrets in repo
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `.github/workflows/**`
- `docs/HOSTING.md` / deploy docs

## Notes / Follow-ups
- Frontend build job later.
