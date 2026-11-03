# Change Report: Module 1 edge authz tests (item 31)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 31

## 1. Summary
Added 2 edge authz tests to `backend/tests/api/test_auth.py` covering Module_1 §18 gaps that item 28 didn't cover. Full suite now 40 passed (was 38).

## 2. Before
- `test_login_inactive_staff_denied` existed (inactive staff), but no test for **deactivated tenant** host → staff locked out
- No test that **staff sessions are rejected by SaaS endpoints** (scope separation between super-admin and tenant staff)

## 3. After
- `test_deactivated_tenant_denied` (test_auth.py): deactivate tenant → login on tenant host → **404** via TenantResolveMiddleware (`Tenant inactive.`) before the view runs
- `test_staff_session_rejected_by_saas_endpoint` (test_auth.py): staff logged in → `GET /api/saas/auth/me/` on `saas.test` host → **403** (SaaSSessionAuthentication finds no `saas_user_id`; IsSaasUser denies)
- Full suite: `pytest -q` → **40 passed** (38 + 2 new), 0 failed

## 4. Reason
Module_1 deep-spec §18 edge scenarios — deactivated tenant access policy + SaaS vs tenant staff scope separation. These were flagged in the project audit as unverified.

## 5. Files Affected
- `backend/tests/api/test_auth.py`
- `work/changes/2026-11-03--31-m1-edge-authz-tests.md`
- `work/completed/31-m1-edge-authz-tests.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`

## 6. Verification / Status
- [x] `pytest -q` → 40 passed, 0 failed (previously 38)
- [x] Deactivated tenant host → 404 (login refused before view)
- [x] Staff session → SaaS endpoint → 403
- [x] Change report written; PLAN/SUMMARY/MEMORY updated
- [x] Completion mail sent to anhali736@gmail.com

## 7. Usage
```bash
bash scripts/todo-notify.sh 31   # already sent
docker compose -f docker-compose.dev.yml exec web python -m pytest -q
```
