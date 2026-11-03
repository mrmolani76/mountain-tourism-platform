# Change Report: eQMS-style module-wise migrations

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: 71

## 1. Summary
Moved Module 1 domain migrations into `Modules/{Saas,Core}/Database/migrations` like eQMS Complere; root `database/migrations` keeps framework tables only.

## 2. Before
- Central: `database/migrations/2026_11_03_000001_*` + `000002_saas_user`
- Tenant: `database/migrations/tenant/2026_11_03_000001_*` + `000002_*`
- Empty `Modules/*/Database/Migrations/.gitkeep`

## 3. After (eQMS layout)
| Scope | Path |
|-------|------|
| Landlord SaaS | `Modules/Saas/Database/migrations/*.php` |
| Tenant Core | `Modules/Core/Database/migrations/tenant/*.php` |
| Framework only | `database/migrations/` (users/cache/jobs/sanctum) |

- `config/tenancy.php` → `migration_parameters.--path` globs `Modules/*/Database/migrations/tenant`
- Providers load landlord module migrations; tenant path via stancl `tenants:migrate`
- Tenant migrations use plain `Schema::create` (connection switched by tenancy), not `Schema::connection('tenant')`

## 4. Reason
User: write migrations module-wise like EQMS.

## 5. Files Affected
- `backend-laravel/Modules/Saas/Database/migrations/*`
- `backend-laravel/Modules/Core/Database/migrations/tenant/*`
- `backend-laravel/Modules/{Saas,Core}/Providers/*ServiceProvider.php`
- `backend-laravel/config/tenancy.php`
- `backend-laravel/database/migrations/README.md`
- Removed: `database/migrations/2026_11_03_000001_*`, `000002_saas_user`, `database/migrations/tenant/*`

## 6. Verification / Status
- [ ] Not migrated on this machine (no setup)
- [x] Layout matches eQMS CLAUDE: module tenant under `migrations/tenant/`; Saas landlord under module `migrations/`
