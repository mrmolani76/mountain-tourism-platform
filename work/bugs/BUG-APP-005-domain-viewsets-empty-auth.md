---
id: BUG-APP-005
title: Domain ViewSets use empty authentication_classes (auth bypass risk)
status: VERIFIED
type: AUTH
severity: high
created: 2026-11-03
---

# BUG-APP-005: Domain ViewSets use empty authentication_classes

## 1. Summary
All tenant domain ViewSets (hotel, folio, pos, rental, services, transport, weather, reports) declare `authentication_classes = []` while requiring `IsAuthenticated`. With no authenticator configured, unauthenticated requests are denied with 403 (PermissionDenied) instead of 401 (NotAuthenticated), and there is no `WWW-Authenticate` challenge. Staff session binding (BUG-TENANT-001) is also not enforced because authentication never runs.

## 2. Environment
Local dev stack, branch main, commit e66fff0f. Django 5.2, DRF. Tests run against physical scratch tenant DBs `lab_test`/`other_test` via aliases `tenant_1`/`tenant_2`.

## 3. Steps to Reproduce
1. Send a GET to any domain list endpoint without a session/cookie, e.g. `GET /api/buildings/` with `HTTP_HOST: lab.test`.
2. Observe status code.

## 4. Expected
Unauthenticated request → HTTP 401 with `WWW-Authenticate: Session realm="api"` challenge (StaffSessionAuthentication defines authenticate_header).

## 5. Actual
Unauthenticated request → HTTP 403 PermissionDenied (no authenticator ran, so `request.authenticators` is empty and `permission_denied` falls through to PermissionDenied).

## 6. Evidence
Source: every domain ViewSet in `backend/modules/{hotel,folio,pos,rental,services,transport,weather,reports}/views/*.py` set `authentication_classes = []` + `permission_classes = [IsAuthenticated, ...]`. DRF `permission_denied` returns 403 when `request.authenticators` is empty. Fixed by replacing with `authentication_classes = [StaffSessionAuthentication]` on all 20 domain ViewSets; `manage.py check` clean; full suite 46 passed (single failure `test_owner_cannot_reach_other_tenant_building` is pre-existing on clean `main`, unrelated).

Verified after fix:
- unauthenticated `GET` on all 8 domain list endpoints → 401 with `WWW-Authenticate: Session realm="api"`.
- authenticated tenant staff → 200 on list endpoints; manager-only write denied → 403.
- login session binding → session-bound staff can access tenant API.
- SaaS auth unchanged (unauthenticated `/api/saas/auth/me/` → 403; staff session → 403).

## 7. Root Cause
Empty `authentication_classes` disables the globally intended `StaffSessionAuthentication`. Authentication never runs, so `request.user` stays `AnonymousUser` and the session tenant-binding check in `StaffSessionAuthentication` is never invoked.

## 8. Workaround
None (clients cannot reliably distinguish "auth required" from "forbidden").

## 9. Suggested Fix
Replace `authentication_classes = []` with `authentication_classes = [StaffSessionAuthentication]` on every domain ViewSet in the 8 modules. Keep `permission_classes = [IsAuthenticated, ...]` unchanged. Do not touch `saas/views/api.py:27` (SaaSLoginView stays public AllowAny login).

## 10. Impact
All tenant domain APIs (folio/charge/payment money endpoints included) accept unauthenticated traffic with a misleading 403 instead of rejecting with 401. Tenant session binding is bypassed.

## 11. Related
- Work item: 34
- Change report: `work/changes/2026-11-03--34-domain-viewsets-auth-fix.md` (to be written)
- Other bugs: BUG-TENANT-001 (session binding, depends on auth running)
