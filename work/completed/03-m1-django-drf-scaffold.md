---
title: Django + DRF backend scaffold
id: 03
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 02
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Django + DRF backend scaffold

**Todo list design**: [`work/phases/01-todos/A-core-and-super-admin.md`](../phases/01-todos/A-core-and-super-admin.md) → TODO-03

## What to do (What)
Create `backend/` Django + DRF + Postgres env + health + OpenAPI stub + `.env.example`. No Nuxt.

## Current / Before
No `backend/` app in repo.

## Expected / After
Runnable API project; health OK; env documented.

## Why
Module A API foundation.

## Acceptance Criteria (complete only when all ticked)
- [ ] `backend/` Django project exists
- [ ] DRF installed; Postgres via env
- [ ] `.env.example` complete
- [ ] Health endpoint works
- [ ] OpenAPI stub enabled
- [ ] README local API run notes
- [ ] No frontend scaffold in this item
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/**`
- `.gitignore`
- `README.md`

---
## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Scaffolded `backend/` Django 5.2 + DRF 3.16 project (`config/` settings split base/local/prod, `apps.core`, health endpoint, OpenAPI schema stub). Postgres via env vars with sqlite local fallback. `.env.example`, `.gitignore`, `README.md` added. `manage.py check` and `migrate` pass; `/api/health/` returns OK; schema + api-root enforce auth.
- **Files changed**: `backend/` (new), `work/PLAN.md`, `work/SUMMARY.md`
- **Verification**: `python3 manage.py check` (0 issues); `migrate` applied; `curl /api/health/` → `{"status":"ok"}`; schema/api-root → 403 without auth.
- **Change report**: `work/changes/2026-11-02--03-m1-django-drf-scaffold.md`
- **PLAN / SUMMARY**: updated yes
