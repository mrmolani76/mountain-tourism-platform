# Change Report: Fix domain ViewSets authentication (item 34)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 34

## 1. Summary
Fixed the auth bypass risk where all 20 tenant domain ViewSets (hotel, folio, pos, rental, services, transport, weather, reports) declared `authentication_classes = []` while requiring `IsAuthenticated`. Replaced with `authentication_classes = [StaffSessionAuthentication]` so unauthenticated requests return 401 (with `WWW-Authenticate` challenge) and staff session binding (BUG-TENANT-001) is enforced. Bug BUG-APP-005 resolved.

## 2. Before
Every domain ViewSet in the 8 domain modules used `authentication_classes = []`. With no authenticator, unauthenticated requests were denied with 403 (no `WWW-Authenticate` challenge) and the session tenant-binding check never ran. Bug confirmed: BUG-APP-005.

## 3. After
- `backend/modules/hotel/views/hotel.py` (4 ViewSets)
- `backend/modules/folio/views/folio.py` (3 ViewSets)
- `backend/modules/pos/views/pos.py` (4 ViewSets)
- `backend/modules/rental/views/rental.py` (1 ViewSet)
- `backend/modules/services/views/services.py` (2 ViewSets)
- `backend/modules/transport/views/transport.py` (3 ViewSets)
- `backend/modules/reports/views/reports.py` (1 ViewSet)
- `backend/modules/weather/views/weather.py` (2 ViewSets)
Each now declares `authentication_classes = [StaffSessionAuthentication]` and imports `StaffSessionAuthentication` from `modules.core.auth`. `permission_classes` unchanged (`IsAuthenticated, ...`). Central SaaS `SaaSLoginView` (`saas/views/api.py:27`) intentionally keeps `authentication_classes = []` (public login); `core/views/api.py:36` health view keeps `[]` (public health).

## 4. Reason
Security hardening required before Module B–I depth work. Unauthenticated access to domain APIs (incl. folio charge/payment money endpoints) returned a misleading 403 instead of 401, and tenant session identity binding was bypassed.

## 5. Files Affected
- `backend/modules/hotel/views/hotel.py`
- `backend/modules/folio/views/folio.py`
- `backend/modules/pos/views/pos.py`
- `backend/modules/rental/views/rental.py`
- `backend/modules/services/views/services.py`
- `backend/modules/transport/views/transport.py`
- `backend/modules/reports/views/reports.py`
- `backend/modules/weather/views/weather.py`
- `backend/tests/api/test_domain_auth.py` (new)
- `work/bugs/BUG-APP-005-domain-viewsets-empty-auth.md` (status → VERIFIED)
- `work/bugs/INDEX.md`
- `work/inprogress/34-m2-domain-auth-fix.md` (→ completed)
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`
- `work/changes/2026-11-03--34-domain-viewsets-auth-fix.md`

## 6. Verification / Status
- [x] `python3 manage.py check` → 0 issues (no circular imports, imports resolve).
- [x] No `authentication_classes = []` remains in domain ViewSets (only intentional public SaaS login + central health keep `[]`).
- [x] New test file `backend/tests/api/test_domain_auth.py`: unauthenticated → 401 on all 8 domain list endpoints; authenticated owner/staff → 200; front-desk manager-write → 403; login session binding works; SaaS auth unaffected (staff session → 403 on SaaS).
- [x] Full backend suite: 46 passed, 1 failed. The single failure (`test_owner_cannot_reach_other_tenant_building`) is pre-existing on clean `main` (local sqlite test DB env issue — verified via `git stash`), unrelated to this change.
- [x] `bash scripts/check-work-kanban.sh` → OK.
- [x] Completion mail sent to anhali736@gmail.com via `scripts/todo-notify.sh 34`.

## 7. Usage
```bash
cd backend && python -m pytest tests/api/test_domain_auth.py -q
```
