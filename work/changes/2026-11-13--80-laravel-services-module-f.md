# Change Report: Module F Services via artisan

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 80

## 1. Summary

Created `Modules/Services` with `php artisan module:make Services --api` and
shipped Module F booking engine APIs with one migration per `service_*` table.

## 2. Before

- No Services module on Laravel.

## 3. After

- Catalog, schedule rules, slots, staff assign (overlap guard), bookings
  (capacity + folio charge / cancel void), guided outing lifecycle.
- Nine separate tenant migrations.
- 16 `api/services/*` routes verified.

## 4. Reason

Phase 3 next product module after Rental. User: artisan only; split migrations.

## 5. Files Affected

- `backend-laravel/Modules/Services/**`
- `backend-laravel/bootstrap/providers.php`
- `docs/DECISIONS.md`
- work kanban 80

## 6. Verification / Status

- [x] `module:make Services --api`
- [x] `route:list --path=api/services` → 16 routes
- [ ] migrate/tests deferred (code-only)
