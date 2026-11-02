# Change Report: Auth API — login, roles, outlet access, password reset

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: work item 05

## 1. Summary
Implemented Auth API for Module A: email/password login → session, me endpoint, logout, owner/manager password reset (no email), Staff model with PDF §3 roles, StaffOutletAccess, tenant/inactive-staff/tenant denial, cross-tenant reset blocked.

## 2. Before
No auth endpoints. Staff model had email/password/tenant but no role or outlet access.

## 3. After
- `backend/apps/core/auth.py` — StaffSessionAuthentication (reads staff_id from session, handles missing session)
- `backend/apps/core/models/staff.py` — role (StaffRole enum, PDF §3), last_login, all_objects (unscoped manager for login), StaffOutletAccess model, can_reset_password property
- `backend/apps/core/models/outlet.py` — minimal Outlet model stub (expanded in item 06)
- `backend/apps/core/models/__init__.py` — export StaffRole, StaffOutletAccess, Outlet
- `backend/apps/core/views.py` — login, logout, me, reset-password, staff CRUD, outlet-access, tenants
- `backend/apps/core/serializers.py` — LoginSerializer, MeSerializer, PasswordResetSerializer, StaffOutletAccessSerializer
- `backend/apps/core/urls.py` — /api/auth/login, /api/auth/logout, /api/auth/me, /api/auth/reset-password, /api/staff, /api/outlet-access, /api/tenants
- `backend/apps/core/migrations/0003_staff_role_outlet_staffoutletaccess.py`
- `backend/config/settings/base.py` — DEFAULT_AUTHENTICATION_CLASSES = StaffSessionAuthentication
- `backend/tests/test_auth.py` — 11 auth tests (login success/fail, inactive, me, logout, reset by owner/manager/front-desk 403, cross-tenant 403)

## 4. Reason
PDF §5A login and staff access. Module A auth foundation. Rules: password reset without email, owner+manager only, tenant isolation, inactive staff/tenant denied.

## 5. Files Affected
- `backend/apps/core/auth.py` (new)
- `backend/apps/core/models/staff.py` (modified)
- `backend/apps/core/models/outlet.py` (new)
- `backend/apps/core/models/__init__.py` (modified)
- `backend/apps/core/views.py` (modified)
- `backend/apps/core/serializers.py` (modified)
- `backend/apps/core/urls.py` (modified)
- `backend/apps/core/migrations/0003_staff_role_outlet_staffoutletaccess.py` (new)
- `backend/config/settings/base.py` (modified)
- `backend/tests/test_auth.py` (new, 11 tests)
- `work/completed/05-m1-auth-roles-api.md` (moved inprogress → completed)
- `work/PLAN.md`, `work/SUMMARY.md` (sync)

## 6. Verification / Status
- [x] `python3 manage.py check` — clean
- [x] `python3 manage.py migrate` — applied (core.0003)
- [x] `python3 manage.py test tests.test_auth tests.test_tenant_isolation` — 16/16 OK
- [x] Kanban check passed
- [x] PLAN.md + SUMMARY.md updated
