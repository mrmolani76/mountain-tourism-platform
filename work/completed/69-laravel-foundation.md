---
title: Laravel foundation (Stage B) — backend-laravel scaffold
id: 69
priority: high
status: completed
milestone: none
module: A-core
area: backend
effort: L
dependencies: 68
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Laravel foundation (Stage B)

## What to do (What)
Create the Laravel application target per master plan Stage B (client-approved 2026-11-03):
- Verify PHP version + required extensions vs selected Laravel release (master plan §25.2)
- composer create-project laravel/laravel backend-laravel (separate dir; Django untouched)
- Configure PostgreSQL connection (.env.example only committed; real .env never)
- php artisan install:api; routes survive; docs/DECISIONS.md updated
- Health endpoint (mirror GET /api/health/ behavior)
- Response envelope + pagination macros to match existing contract (audit §2)
- Update CI (composer validate, php -l/static analysis; NO test suites per master plan §16)

## Current / Before
docs/LARAVEL_SWITCH_AUDIT.md + docs/LARAVEL_SWITCH_MASTER_PLAN.md exist (item 68).
No Laravel code in repo. Phase 1 foundation checklist of client master plan unchecked.

## Expected / After
backend-laravel/ boots locally with pgsql connection; health endpoint returns the standard
envelope; CI quality checks (no tests) updated; README/docs updated; audit inventory gains
Laravel target column entries for pilot endpoints.

## Why
Master plan Stage B; client directive 2026-11-03 "ye kaam hai rework".

## Acceptance Criteria (complete only when all ticked)
- [ ] PHP runtime + extensions verified (report exact versions)
- [ ] backend-laravel/ created; artisan boot verified (php artisan about)
- [ ] PostgreSQL connection configured (.env.example updated, no secrets)
- [ ] install:api + route:list recorded
- [ ] Health endpoint with success envelope
- [ ] Response envelope/pagination macros scaffolded
- [ ] CI updated: no test suites; composer/quality checks only
- [ ] Django backend untouched; tests in repo preserved
- [ ] Change report + trackers updated


## Completion Log

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Stage B complete setup per client directive ("han and complete setup krke dena"):
  1. PHP 8.5.4 + Composer 2.9.5 + 13 required extensions installed (Ubuntu apt, master plan §25.2).
  2. `composer create-project laravel/laravel backend-laravel` → Laravel 13.39.0 boot verified (`artisan about`).
  3. PostgreSQL 18.6 configured: local dev DB `mountain_tourism` + dedicated role; PG18 note — `GRANT ALL ON SCHEMA public` required before first migrate. Real creds ONLY in gitignored `.env`; `.env.example` placeholders.
  4. `php artisan migrate` (4 core migrations ran on dev DB) + `php artisan install:api` (Sanctum) — routes recorded.
  5. Contract preserved: `Response::api()` / `Response::apiError()` macros in AppServiceProvider match DRF envelope `{success,data,error,meta}` (audit §2); CORS published with FRONTEND_URL env.
  6. Health endpoint `GET /api/health` (public) — live-tested 200: `{"success":true,"data":{app,environment,database:ok,time},"error":null,"meta":{}}`.
  7. CI: `laravel` quality job added to `.github/workflows/ci.yml` — composer validate, platform reqs, php -l over app/routes/database/config, boot check, test-folder presence WITHOUT running tests (master plan §16 guard). Django quality gate untouched.
  8. Docs: `docs/LARAVEL_LOCAL_SETUP.md`; update to `docs/LARAVEL_SWITCH_MASTER_PLAN.md` §6 checklist; `docs/DECISIONS.md` decision row.
- **Files changed**: backend-laravel/ (new app), .github/workflows/ci.yml, docs/LARAVEL_LOCAL_SETUP.md (new), docs/DECISIONS.md, docs/LARAVEL_SWITCH_MASTER_PLAN.md, work trackers.
- **Verification**: artisan about (13.39.0), db:show (pgsql connect), migrate:status all Ran, route:list, live curl health 200, php -l per file incl. new built files. NO tests run per master plan §16 (local verification only). No destructive ops; backend/ Django untouched; deploy.yml intentionally untouched until Stage E.
- **Change report**: work/changes/2026-11-03--69-laravel-foundation.md
- **PLAN / SUMMARY / MEMORY**: updated
