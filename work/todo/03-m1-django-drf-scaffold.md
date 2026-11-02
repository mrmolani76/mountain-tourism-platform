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

## What to do (What)
Create `backend/` with Django, DRF, PostgreSQL settings, project layout, health endpoint, `.env.example`, basic README for local API run. **No Nuxt/frontend scaffold.**

## Current / Before
Repo has docs/rules only; no `backend/` app.

## Expected / After
Runnable Django+DRF project; Postgres config via env; OpenAPI stub optional; `.env.example` documented.

## Why
API-first foundation for all later modules.

## Acceptance Criteria (complete only when all ticked)
- [ ] `backend/` boots locally against Postgres
- [ ] DRF installed; health or root API responds
- [ ] `.env.example` lists required vars
- [ ] No frontend package added in this item
- [ ] Change report + PLAN/SUMMARY updated

## Files Likely Affected
- `backend/**`
- `.gitignore`
- `README.md` (API local setup section)

## Notes / Follow-ups
- Frontend stays out of scope until user opens frontend focus.
