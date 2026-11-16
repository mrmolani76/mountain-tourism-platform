# Change Report: Nexro-strict central DB tables

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 94

## 1. Summary

Central landlord DB no longer keeps unused Laravel `users` / `password_reset_tokens` (Nexro: shop users are not on central).

## 2. Before

- Skeleton migration created `users`, `password_reset_tokens`, and `sessions` on central
- Product auth already uses `saas_user` (central) and `core_staff` (tenant)

## 3. After

- New migration drops `users` and `password_reset_tokens` on central
- Kept: `sessions`, cache, jobs, Sanctum tokens + `saas_*`
- README lists strict central allowlist

## 4. Reason

User: central has too many tables; make strictly like Nexro; code only.

## 5. Files Affected

- `backend-laravel/database/migrations/2026_11_14_000001_drop_central_users_nexro_strict.php`
- `backend-laravel/database/migrations/README.md`
- `work/completed/94-nexro-strict-central-db-tables.md`
- `work/PLAN.md`, `work/SUMMARY.md`

## 6. Verification / Status

- [x] Migrate run on setup machine (Docker central): drop applied 2026-11-14
- [x] Central now 11 tables: sessions, cache, jobs, tokens + `saas_*`
- Notes: verified on Docker central (`mountain_tourism`); tenant `lab_local` intact (8 staff), `/api/health/` 200.
