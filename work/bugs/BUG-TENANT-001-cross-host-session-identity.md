---
id: BUG-TENANT-001
title: Session staff_id resolves against whichever tenant DB the Host selects
status: IN_PROGRESS
type: TENANT
severity: medium
created: 2026-11-02
---

# BUG-TENANT-001: Cross-host session identity swap risk

## 1. Summary
`StaffSessionAuthentication` reads `session['staff_id']` and fetches Staff by plain
`pk` on the **currently active tenant DB**. A session issued on tenant A, replayed
against tenant B's host, authenticates as whatever Staff row has the same `pk` in B's DB.
Browser cookie scoping (different hostnames) makes exploitation unlikely in practice,
but API-level defense in depth is missing.

## 2. Environment
Backend `main` after pull to `424e9d2`. Files: `backend/modules/core/auth.py`,
`backend/tenancy/middleware.py`.

## 3. Steps to Reproduce
1. Login on `a.test` as owner of tenant A; session stores `staff_id=N`.
2. Send the same session cookie to `other.test` (tenant B) where Staff `pk=N` exists.
3. `StaffSessionAuthentication.authenticate()` loads B's Staff row N and returns it as identity.

Automated variant: DRF `force_authenticate(user=staff_a)` with `HTTP_HOST=other.test`
passes `IsTenantStaff` and hits tenant B data.

## 4. Expected
A staff session issued by tenant A must never authenticate on any other tenant host,
regardless of pk collisions (return 401/403).

## 5. Actual
Authentication succeeds on a foreign host when the pk collides; the acting identity
silently switches databases.

## 6. Evidence
Code inspection: `StaffSessionAuthentication.authenticate()` (`staff = Staff.objects.get(pk=staff_id)`),
no tenant binding stored in session. Found while writing item 06 cross-tenant tests
(`backend/tests/test_core_config.py`).

## 7. Root Cause
Sessions store only `staff_id` (no tenant id / no signed tenant binding), and identity
resolution is not pinned to the tenant that issued the session.

## 8. Workaround
None at API level. Browser-level hostname cookie isolation reduces exposure.

## 9. Suggested Fix
Follow-up work item: store tenant id/slug in the session at login
(`/api/auth/login/`), and in `StaffSessionAuthentication` reject the request when
session tenant != current tenant (before Staff lookup). Add test:
session issued on A, then a cross-host request returns 401/403.

## 10. Impact
Defense in depth on tenant isolation (Rule 3). No direct money/folio impact yet
(Module A scope).

## 11. Related
- Work item: 06 (found during tests), TODO-04 isolation layer
- Change report: `work/changes/2026-11-02--06-core-config-apis.md` (pending)
- Other bugs: none
