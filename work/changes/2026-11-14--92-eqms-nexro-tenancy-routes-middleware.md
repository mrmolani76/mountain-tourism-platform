# Change Report: EQMS/Nexro-style tenancy routes + middleware

- **Date**: 2026-11-14
- **By**: Agent
- **Change ID**: 92

## 1. Summary

Mirrored EQMS/Nexro central vs tenant route and middleware separation while keeping the App\Tenancy Host→DB bridge. No setup runs.

## 2. Before

- `ResolveTenant` prepended on every `api` request
- Saas and tenant modules shared the same middleware stack
- No `PreventAccessFromCentralDomains` / `PreventAccessFromTenantDomains`
- Domain modules called `loadMigrationsFrom` on parent dirs that only contain `tenant/`

## 3. After

- New middleware: prevent central, prevent tenant, ensure tenant resolved
- Saas routes domain-bound to `tenancy.central_domains` + `tenancy.prevent_tenant`
- Tenant module routes: prevent_central → resolve.tenant → ensure_resolved
- Landlord `loadMigrationsFrom` only when top-level `*_*.php` exist
- Docs note in `MULTI_TENANT_LOCAL.md`

## 4. Reason

User asked to implement tenancy code the way EQMS and Nexro do (routes + middleware separation).

## 5. Files Affected

- `backend-laravel/app/Http/Middleware/PreventAccessFromCentralDomains.php`
- `backend-laravel/app/Http/Middleware/PreventAccessFromTenantDomains.php`
- `backend-laravel/app/Http/Middleware/EnsureTenantResolved.php`
- `backend-laravel/app/Http/Middleware/ResolveTenant.php`
- `backend-laravel/app/Http/Middleware/SaasSessionAuth.php`
- `backend-laravel/bootstrap/app.php`
- `backend-laravel/Modules/Saas/App/Providers/RouteServiceProvider.php`
- `backend-laravel/Modules/*/App/Providers/RouteServiceProvider.php` (tenant modules)
- `backend-laravel/Modules/*/App/Providers/*ServiceProvider.php` (migration gate)
- `docs/MULTI_TENANT_LOCAL.md`
- `work/PLAN.md`, `work/SUMMARY.md`, work item 92

## 6. Verification / Status

- [x] Code edited (coding-agent: no migrate/serve/Apache)
- [ ] Runtime browser verify deferred (setup out of scope)
- [x] Notes: full stancl InitializeTenancyByDomain still deferred
