# Root migrations (landlord framework only)

Keep Laravel skeleton / framework tables here (`users`, `cache`, `jobs`, Sanctum tokens).

**Domain migrations are module-wise (eQMS Complere):**

| Scope | Path | Command |
|-------|------|---------|
| Central SaaS | `Modules/Saas/Database/migrations/` | `php artisan migrate` |
| Tenant Core | `Modules/Core/Database/migrations/tenant/` | `php artisan tenants:migrate` |

Do not add hotel/folio/… tables under this folder — put them in that module’s `Database/migrations/tenant/`.
