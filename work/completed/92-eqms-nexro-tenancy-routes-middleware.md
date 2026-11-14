---
title: EQMS/Nexro-style tenancy routes + middleware
id: 92
priority: high
status: completed
milestone: none
module: A-core
area: backend
effort: M
dependencies: 91
bug: none
created: 2026-11-14
updated: 2026-11-14
---

# EQMS/Nexro-style tenancy routes + middleware

## What to do (What)

Mirror EQMS/Nexro central vs tenant route/middleware separation using existing App\Tenancy bridge. No setup runs.

## Current / Before

- ResolveTenant prepended on all api
- Saas and tenant modules share middleware stack
- No PreventAccessFromCentralDomains / PreventAccessFromTenantDomains

## Expected / After

- Saas domain-bound to central_domains + prevent tenant hosts
- Tenant modules: resolve.tenant + prevent_central
- Domain modules stop loadMigrationsFrom empty landlord parents

## Why

User: implement tenancy code like EQMS and Nexro.

## Acceptance Criteria (complete only when all ticked)

- [x] Middleware aliases registered
- [x] Saas + tenant RouteServiceProviders updated
- [x] Migration loading hygiene
- [x] Change report + PLAN/SUMMARY
- [x] No setup commands run

## Files Likely Affected

- `backend-laravel/app/Http/Middleware/*`
- `backend-laravel/bootstrap/app.php`
- `backend-laravel/Modules/*/App/Providers/*`

## Notes / Follow-ups

- Full stancl InitializeTenancyByDomain deferred.


---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-14
- **What was done**: EQMS/Nexro-style route+middleware split; landlord migration gate; docs note.
- **Verification**: Code-only; no setup commands.
- **Change report**: `work/changes/2026-11-14--92-eqms-nexro-tenancy-routes-middleware.md`
