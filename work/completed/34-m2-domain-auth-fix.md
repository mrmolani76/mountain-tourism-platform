---
title: Fix domain ViewSets auth (hotel…reports)
id: 34
priority: high
status: completed
milestone: 1
module: A-core
area: backend
effort: M
dependencies: 33
bug: none
created: 2026-11-03
updated: 2026-11-03
---

# Fix domain ViewSets auth (hotel…reports)

## What to do (What)
BUG / auth: domain modules (hotel, folio, pos, rental, services, transport, weather, reports) must not use empty authentication_classes. Align with core staff auth.

Deep spec: `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`

## Current / Before
Audit: several domain ViewSets set authentication_classes = []

## Expected / After
All domain ViewSets require staff auth; unauthenticated → 401; tests cover one endpoint per module or shared base

## Why
Security / Module B–I prerequisite

## Acceptance Criteria (complete only when all ticked)
- [ ] Deliverable described in After is implemented (API/backend; no Nuxt unless user asks)
- [ ] Relevant automated tests added or updated (or N/A with reason if code-only run forbidden)
- [ ] `work/changes/YYYY-MM-DD--34-<desc>.md` written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/*/views/`
- `backend/tests/`

## Notes / Follow-ups
- Product/API code only. No Docker/VPS/CI/setup unless user asks.
- WIP = 1. Pickup before edits.
- Frontend screens deferred.

---

## Return to Pending Log (when work stops midway)

(none yet)

---

## Completion Log (when work is complete)

- **Completed by**: OpenCode agent
- **Date**: 2026-11-03
- **What was done**: Replaced `authentication_classes = []` with `authentication_classes = [StaffSessionAuthentication]` on all 20 domain ViewSets across 8 modules (hotel, folio, pos, rental, services, transport, weather, reports); added `StaffSessionAuthentication` import to each. Left central SaaS login (`saas/views/api.py:27`, public AllowAny) and central health view (`core/views/api.py:36`) untouched. Added `backend/tests/api/test_domain_auth.py` covering unauthenticated→401, authenticated→200, role-denied→403, session binding, SaaS isolation for all 8 domain modules. Bug BUG-APP-005 resolved and verified.
- **Files changed**: 8 view files + `backend/tests/api/test_domain_auth.py` (new) + `work/bugs/BUG-APP-005-*.md` + `work/bugs/INDEX.md` + `work/changes/2026-11-03--34-domain-viewsets-auth-fix.md` + `work/PLAN.md` + `work/SUMMARY.md` + `work/MEMORY.md`
- **Verification**: `manage.py check` clean; `pytest` → 46 passed, 1 failed (pre-existing `test_owner_cannot_reach_other_tenant_building`, fails on clean `main`, unrelated); `check-work-kanban.sh` OK.
- **Change report**: `work/changes/2026-11-03--34-domain-viewsets-auth-fix.md`
- **PLAN / SUMMARY**: updated yes
