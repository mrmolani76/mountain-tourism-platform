# Change Report: Laravel foundation Stage B (item 69)

- **Date**: 2026-11-03
- **Work item**: 69
- **By**: OpenCode agent
- **Directive**: client approved "complete setup krke dena"

## Summary

Full Stage B Laravel foundation delivered. New `backend-laravel/` application (Laravel 13.39.0,
PHP 8.5.4) boots and connects to PostgreSQL 18.6 with Sanctum API scaffolding installed.
The existing DRF response envelope contract is preserved via Laravel response macros, and a
public health endpoint is live and tested. CI gains a Laravel quality (non-test) job.

## Before

- Repo had only Django (`backend/`) + audit/master-plan docs (item 68).
- No PHP/Composer runtime on the machine; no Laravel code.

## After

| Piece | Detail | Verified by |
|---|---|---|
| Runtime | PHP 8.5.4, Composer 2.9.5, extensions pdo_pgsql/pgsql/mbstring/xml/curl/zip/bcmath/tokenizer/openssl/fileinfo/ctype/dom/intl | `php -v`, `php -m` |
| Laravel app | `backend-laravel/` — Laravel 13.39.0 | `php artisan about` |
| PostgreSQL | `pgsql` connection, dev DB `mountain_tourism`; PG18 public-schema grant documented | `artisan db:show` (PostgreSQL 18.6) |
| API support | `install:api` (Sanctum; `personal_access_tokens` migration Ran) | `route:list`, `migrate:status` |
| Envelope contract | `Response::api()` / `Response::apiError()` — exact DRF shape `{success,data,error,meta}` | live curl |
| Health endpoint | `GET /api/health` public incl. DB probe | 200 + JSON verified |
| CORS | `config/cors.php` published; origin via `FRONTEND_URL` env | config present |
| CI | `laravel` job: composer validate + check-platform-reqs + `php -l` + boot check + tests-folder present guard (NEVER runs suites, §16) | YAML valid |
| Secrets | `.env` gitignored; `.env.example` placeholder-only (matches master plan §18 sample) | `git check-ignore`, grep |
| Docs | `docs/LARAVEL_LOCAL_SETUP.md`; master plan §6 checklist ticked; DECISIONS row | — |

## Intentionally NOT done (per master plan)

- **Django untouched** — no code removed, tests stay in repo, CI Django gate unchanged (Rule 6, §25.8).
- **deploy.yml untouched** — Laravel deploy rework happens in Stage E (deploying an empty Laravel
  app now would regress the running service).
- **No endpoint ported yet** — Stage C does vertical slices from the item-68 audit inventory.
- **No production migration** — `migrate` ran only against the freshly created local dev DB.
- **No `/api/v1` rename, no integer-minor-units change** — §22 open client decisions.

## Verification (executed)

1. `php artisan --version` → Laravel 13.39.0
2. `php artisan db:show` → PostgreSQL 18.6 connected
3. `php artisan migrate --force` → 4/4 Ran on dev DB
4. `php artisan route:list` → api/health + api/user + sanctum routes
5. `curl http://127.0.0.1:8110/api/health` → 200, exact envelope
6. `php -l` → No syntax errors on all new/modified PHP files
7. `python3 -c yaml.safe_load(ci.yml)` → valid
8. `git check-ignore` → `.env`, `vendor/`, `node_modules/` ignored

NO automated test suites were executed anywhere (master plan §16); tests remain in the repo.

## Files affected

- `backend-laravel/**` (new Laravel application; vendor/node_modules/.env gitignored)
- `.github/workflows/ci.yml` (+ laravel quality job)
- `docs/LARAVEL_LOCAL_SETUP.md` (new)
- `docs/LARAVEL_SWITCH_MASTER_PLAN.md` (Stage B checklist ticks)
- `docs/DECISIONS.md` (1 appended row)
- `work/` trackers + this report
