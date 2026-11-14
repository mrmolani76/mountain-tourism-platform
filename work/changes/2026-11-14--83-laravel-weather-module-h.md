# Change Report: Module H Weather (Laravel)

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 83

## 1. Summary

Shipped `Modules/Weather` for Module H: points, MET/Open-Meteo fetch,
limits, warning evaluation, explicit cancel-for-weather voids, avalanche and
lobby stubs.

## 2. Before

- No Weather module on Laravel.
- Transport lifts/trips and Services outdoor slots existed as warning targets.
- Folio already supported `reason_code=weather` voids.

## 3. After

- Ten tenant migrations (`weather_point` … `weather_lobby_screen`).
- Point/limit CRUD; server-side fetch (MET Norway primary, Open-Meteo fallback);
  evaluate → warnings + soft-linked targets; never auto-cancels.
- Cancel-for-weather voids related folio charges via `FolioService::voidEntry`
  with `REASON_WEATHER` and returns guest notify list.
- Avalanche upsert + public lobby-by-token stub.
- Validation via Spatie Data under `App/Data` (eQMS Complere / Hotel pattern);
  no Form Requests.
- Module enabled in `modules_statuses.json` and `bootstrap/providers.php`.

## 4. Reason

User: next after Transport frontend. Phase 4 Module H core.

## 5. Files Affected

- `backend-laravel/Modules/Weather/**`
- `backend-laravel/bootstrap/providers.php`
- `backend-laravel/modules_statuses.json`
- work kanban 83

## 6. Verification / Status

- [x] `php -l` on Weather module PHP (clean)
- [x] Code-only (no migrate / artisan test / network fetch run)
- [ ] Browser / tenant migrate deferred
- Notes: lobby screen staff CRUD deferred (Phase 5); lift pass charges not
  auto-voided on lift weather cancel.
