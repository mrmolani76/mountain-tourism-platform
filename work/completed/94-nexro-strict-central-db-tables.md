---
title: Nexro-strict central DB tables (no Laravel users on landlord)
id: 94
priority: high
status: completed
milestone: none
module: A-core
area: backend
effort: S
dependencies: 93
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# Nexro-strict central DB tables

## What to do (What)

Align central (landlord) schema with Nexro: no `users` / `password_reset_tokens` on central. Keep `saas_*` + framework sessions/cache/jobs/tokens only. Code + migration only; do not run migrate unless asked.

## Current / Before

- Laravel skeleton creates `users` + `password_reset_tokens` + `sessions` on central
- Nexro central has no `users` table on landlord

## Expected / After

- Drop migration for unused central users tables
- README allowlist

## Why

User: strictly like Nexro; code only.

## Acceptance Criteria (complete only when all ticked)

- [x] Drop migration for central `users` + `password_reset_tokens`
- [x] migrations README allowlist updated
- [x] Change report; PLAN + SUMMARY
- [x] No migrate/run on machine (code-only)

## Completion Log

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: Added `2026_11_14_000001_drop_central_users_nexro_strict.php`; README central allowlist
- **Change report**: `work/changes/2026-11-14--94-nexro-strict-central-db-tables.md`
- **Verify**: code-only; human runs `php artisan migrate` later
