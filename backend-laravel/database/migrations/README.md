# Root migrations (landlord / central only)

`php artisan migrate` applies:

1. This folder (`database/migrations/`)
2. `Modules/Saas/Database/migrations/` (loaded by SaasServiceProvider)

## Strict central allowlist (Nexro-style)

| Table | Source |
|-------|--------|
| `saas_tenant` | Saas module |
| `saas_domain` | Saas module |
| `saas_user` | Saas module |
| `sessions` | Laravel skeleton (kept; SaaS/staff cookie sessions) |
| `cache` / `cache_locks` | Laravel skeleton |
| `jobs` / `job_batches` / `failed_jobs` | Laravel skeleton |
| `personal_access_tokens` | Sanctum |

**Not on central (Nexro rule):**

- Laravel `users` / `password_reset_tokens` — dropped by `2026_11_14_000001_drop_central_users_nexro_strict.php`
- Any `core_*`, `hotel_*`, `folio_*`, `pos_*`, … — those live under `Modules/*/Database/migrations/tenant/` and run only on tenant DBs (`tenants:provision-local` / tenancy `migration_parameters`)

## Tenant schema

| Scope | Path | How |
|-------|------|-----|
| Central SaaS | `Modules/Saas/Database/migrations/` | `php artisan migrate` |
| Tenant modules | `Modules/*/Database/migrations/tenant/` | `php artisan tenants:provision-local` (or migrate with `--database=tenant` + those paths) |

Do not put hotel/folio/… tables in this folder.
