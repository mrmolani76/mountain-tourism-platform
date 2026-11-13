# Change Report: Module G Transport via artisan

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 81

## 1. Summary

Created `Modules/Transport` with `php artisan module:make Transport --api` and
shipped Module G transport/lifts APIs with one migration per `transport_*` table.

## 2. Before

- No Transport module on Laravel.

## 3. After

- Vehicles, drivers, routes (+ stops), trips with driver/vehicle overlap and seat
  booking, reservation pickup, printable passenger list.
- Lift CRUD, append-only status history, public lobby status API.
- Lift pass products + sale posting Folio charge (`source_type=transport`).
- Ten separate tenant migrations under `Database/migrations/tenant/`.
- 25 `api/transport/*` routes verified.

## 4. Reason

Phase 4 next product module after Services. User: artisan only; split migrations;
code-only; commit when done (no push).

## 5. Files Affected

- `backend-laravel/Modules/Transport/**`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/modules_statuses.json`
- work kanban 81

## 6. Verification / Status

- [x] `module:make Transport --api`
- [x] `route:list --path=api/transport` → 25 routes
- [x] `php -l` on module PHP (clean)
- [ ] migrate/tests deferred (code-only)
