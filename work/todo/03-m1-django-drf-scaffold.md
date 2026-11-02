---
title: Django + DRF backend scaffold
id: 03
priority: high
status: todo
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
