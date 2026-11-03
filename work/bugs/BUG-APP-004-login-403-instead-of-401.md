---
id: BUG-APP-004
title: Staff login with bad credentials returns 403 instead of 401
status: VERIFIED
type: APP
severity: low
created: 2026-11-03
---

# BUG-APP-004: Staff login bad credentials → 403 instead of 401

## 1. Summary
`POST /api/auth/login/` with unknown email or wrong password returned 403 (DRF default when no authenticator defines `authenticate_header`; `LoginView.authentication_classes = []`).

## 2. Environment
Local Docker dev stack; `backend/modules/core/views/api.py`, `backend/modules/core/auth.py`.

## 3. Steps to Reproduce
`POST /api/auth/login/` with `{"email": "nobody@test.com", "password": "x"}` on a tenant host.

## 4. Expected
401 with `WWW-Authenticate` header (RFC semantics: unauthenticated, not forbidden).

## 5. Actual
403 Forbidden.

## 6. Evidence
```
E       AssertionError: 403 != 401
tests/api/test_auth.py:126
```

## 7. Root Cause
DRF maps `AuthenticationFailed` to 401 only when at least one authenticator defines `authenticate_header`; the login view had no authenticators and the custom exception handler does not remap it.

## 8. Workaround
None needed (semantics fix).

## 9. Suggested Fix
Add `authenticate_header` to `StaffSessionAuthentication`; register it on `LoginView` (permission stays `AllowAny`).

## 10. Impact
Misleading status codes for auth failures (semantics only; no data risk).

## 11. Related
- Work item: 28
- Change report: `work/changes/2026-11-03--28-module1-test-run-fixes.md`
