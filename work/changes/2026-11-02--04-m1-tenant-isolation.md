# Change Report: Tenant isolation layer + cross-tenant test

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: work item 04

## 1. Summary
Implemented single tenant-isolation layer (TenantManager + `tenant_id`), Tenant + Staff models, staff/tenant API stubs, migrations, and cross-tenant isolation tests (5/5 passing) — Module A acceptance proof.

## 2. Before
No Tenant model, no isolation layer, no tenant-scoped queries.

## 3. After
- `backend/apps/core/models/base.py` — TenantModel abstract base + TenantManager (single isolation layer, filters by current tenant context).
- `backend/apps/core/models/tenant.py` — Tenant (name, is_active).
- `backend/apps/core/models/staff.py` — Staff (email, password, tenant FK, is_active, last_login).
- `backend/apps/core/middleware.py` — TenantContextMiddleware (sets tenant from authenticated staff user; never trusts client tenant_id).
- `backend/apps/core/views.py` — health, api-root, staff list/detail (tenant-scoped), tenant list.
- `backend/apps/core/serializers.py` — StaffSerializer, TenantSerializer.
- `backend/apps/core/urls.py` — /api/health, /api/staff, /api/staff/<pk>, /api/tenants.
- `backend/apps/core/migrations/0001_initial.py`, `0002_staff_last_login.py`.
- `backend/tests/test_tenant_isolation.py` — 5 cross-tenant isolation tests.
- `backend/config/settings/base.py` — registered TenantContextMiddleware.
- `backend/config/urls.py` — wired core URLs + schema.

## 4. Reason
Module A acceptance (PDF §5A): two complexes cannot see each other's data, proven by automated test. TECHNICAL_RULES §2: isolation in one place.

## 5. Files Affected
- `backend/apps/core/models/{__init__,base,tenant,staff}.py`
- `backend/apps/core/middleware.py`
- `backend/apps/core/views.py`
- `backend/apps/core/serializers.py`
- `backend/apps/core/urls.py`
- `backend/apps/core/migrations/0001_initial.py`
- `backend/apps/core/migrations/0002_staff_last_login.py`
- `backend/tests/test_tenant_isolation.py`
- `backend/config/settings/base.py`
- `backend/config/urls.py`
- `work/completed/04-m1-tenant-isolation.md` (moved inprogress → completed)
- `work/PLAN.md`, `work/SUMMARY.md` (sync)

## 6. Verification / Status
- [x] `python3 manage.py check` — clean
- [x] `python3 manage.py migrate` — applied (core.0001, 0002)
- [x] `python3 manage.py test tests.test_tenant_isolation` — 5/5 OK
- [x] Kanban check passed
- [x] PLAN.md + SUMMARY.md updated
