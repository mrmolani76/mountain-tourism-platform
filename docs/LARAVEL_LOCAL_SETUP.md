# Laravel Backend — Local Setup (Stage B, item 69)

> Master plan: `docs/LARAVEL_SWITCH_MASTER_PLAN.md` · Audit: `docs/LARAVEL_SWITCH_AUDIT.md`
> Django backend (`backend/`) stays intact until Laravel cutover acceptance (master plan Rule 6).

## Current state (2026-11-03)

| Component | Value |
|---|---|
| App directory | `backend-laravel/` (separate; `backend/` untouched) |
| Laravel | 13.39.0 |
| PHP | 8.5.4 (cli) |
| Composer | 2.9.5 |
| Database | PostgreSQL 18.6, connection = pgsql, verified by `db:show` + health endpoint |
| API support | `install:api` done (Sanctum + `sanctum/csrf-cookie` + tokens migration ran) |
| Migrations | 4 core migrations ran on local dev DB |
| Health endpoint | `GET /api/health` — public, envelope contract |
| Response envelope | `Response::api()` / `Response::apiError()` macros matching DRF contract `{success, data, error, meta}` |

## Required PHP extensions (verified installed on dev machine)

`pdo_pgsql, pgsql, mbstring, xml, curl, zip, bcmath, tokenizer, openssl, fileinfo, ctype, dom, intl`

## Local run

```bash
cd backend-laravel
composer install            # if vendor missing
cp .env.example .env        # first time (already done here; .env is gitignored)
php artisan key:generate
php artisan migrate         # local dev DB only — never against production without approval
php artisan serve --port=8000
```

Health check: `curl http://localhost:8000/api/health`

## PostgreSQL (local dev)

- DB `mountain_tourism`, dedicated local role; credentials live only in gitignored `.env`.
- `.env.example` carries placeholder credentials only — never real ones.
- PG 18 note: `GRANT ALL ON SCHEMA public TO <app_user>` is required before first migrate
  (PG 15+ removed implicit PUBLIC create on `public` schema).

## Contract guarantees (per audit §2)

Environment macros registered in `app/Providers/AppServiceProvider.php`:

```php
response()->api($data, $meta, $status)          // success envelope
response()->apiError($code, $message, $details, $status)  // error envelope
```

Envelope shape matches Django `common/responses`; page-number pagination meta
(`page, page_size, total_pages, total_count, has_next, has_previous`) must be preserved when
listing endpoints are ported (Stage C).

## CORS

`config/cors.php` — allowed origin from `FRONTEND_URL` env (default `*` for local only).
For production set a concrete origin; never commit secrets.

## CI policy (master plan §16)

CI must NOT run test suites (`php artisan test`, PHPUnit, Pest). CI runs quality checks only:
composer validate, `php -l` over app code (see `.github/workflows/ci.yml` laravel job).
Tests remain in the repo (`backend-laravel/tests/`) for local/manual verification.

## What is intentionally NOT done yet

- No Django endpoint ported yet (Stage C follows, one vertical slice at a time).
- `/api/v1` versioning: deferred — client decision §22 (current contract ports as-is first).
- Money units: DECIMAL(12,2) preserved (audit conflict #1; client decision).
- No destructive action: Django `backend/`, its tests, and CI quality checks remain in place.
