# Change Report: Module E Equipment rental via artisan

- **Date**: 2026-11-13
- **By**: Agent
- **Change ID**: 79

## 1. Summary

Created `Modules/Rental` with `php artisan module:make Rental --api`, post-fixed
to `App/` convention, and shipped Module E APIs with one migration per table.

## 2. Before

- No `Modules/Rental`.

## 3. After

- Artisan scaffold + Folio-style providers / composer PSR-4.
- Five tenant migrations (equipment_type, equipment_item, season_price, rental, rental_item).
- APIs: types/items/rates CRUD, rent out, return (late + damage fees), currently-out list.
- Money via Folio `source_type = rental`.

## 4. Reason

Next Phase 3 product module after POS. User: modules via artisan only; migrations split per table.

## 5. Files Affected

- `backend-laravel/Modules/Rental/**`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/modules_statuses.json` (artisan enabled Rental)
- `docs/DECISIONS.md`
- work kanban 79

## 6. Verification / Status

- [x] `php artisan module:make Rental --api`
- [x] `module:list` shows Rental enabled
- [x] `route:list --path=api/rental` → 13 routes
- [ ] migrate / tests not run (code-only)
