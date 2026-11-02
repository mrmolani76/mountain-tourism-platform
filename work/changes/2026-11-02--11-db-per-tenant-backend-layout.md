# Change Report: DB-per-tenant backend layout (DRF)

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 11

## 1. Summary
Replaced shared-DB `tenant_id` isolation with EQMS-style DB-per-tenant layout: `tenancy/`, central `apps.saas`, domain app stubs, DRF ViewSets/routers. Code only — no migrate/createdb.

## 2. Before
- Shared SQLite/Postgres + `TenantManager` / `tenant` FK on Staff/Outlet
- Function-based core views; Tenant model in `apps.core`
- Isolation tests assumed same DB row filter

## 3. After
- Central: `apps.saas` (Tenant, Domain, SaasUser) + `/api/saas/` DRF ViewSets
- Tenant: `apps.core` Staff/Outlet without `tenant_id`; DRF APIViews + ViewSets
- Platform: `tenancy/` middleware, context, connections, provision stubs
- Domain stubs: hotel, folio, pos, rental, services_app, transport, weather, reports
- Docs/CLAUDE/DECISIONS updated; SSO explicitly out

## 4. Reason
User asked EQMS-like per-tenant DB backend structure, DRF-aligned, frontend deferred, SSO out, code only (no setup).

## 5. Files Affected
- `backend/tenancy/**`
- `backend/apps/saas/**`
- `backend/apps/core/**` (models, views, auth, urls, services, migration 0004)
- `backend/apps/{hotel,folio,pos,rental,services_app,transport,weather,reports}/**`
- `backend/config/{settings,urls,db_router}.py`
- `backend/tests/test_auth.py`, `test_tenant_db_isolation.py`, `test_saas_registry.py`
- `docs/ARCHITECTURE.md`, `docs/DECISIONS.md`, `docs/TECHNICAL_RULES.md`
- `CLAUDE.md`, coding-agent + django-best-practices skills (tenancy lines)

## 6. Verification / Status
- [ ] Tested / Verified? No — user: code only, no setup/test run
- [x] Migrations/Scripts pending? Yes — apply `saas.0001`, `core.0004`, createdb per tenant later
- [x] Notes: Physical provision refused in `tenancy.provision.provision_tenant_database`
