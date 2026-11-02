---
title: folio module — Folio, Charge, Payment models + DRF APIs
id: 19
priority: high
status: completed
milestone: 2
module: C-folio
area: backend
effort: L
dependencies: 18
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# folio module — Folio, Charge, Payment models + DRF APIs

## What to do
Implement Module C (Guest folio and checkout) in `backend/modules/folio/`:
- Models: Folio, Charge, Payment (money = append-only, void with reason + user + timestamp)
- Serializers for all models
- ViewSets: FolioViewSet, ChargeViewSet, PaymentViewSet
- Permissions: role-based (front desk / cashier / owner)
- URLs: register folio routes in config/urls.py
- Services: post_charge, record_payment, void_charge, void_payment

## Current / Before
`backend/modules/folio/` has empty `__init__.py`, `urls.py` (empty urlpatterns), `views/__init__.py` (0 lines), `serializers/__init__.py` (0 lines), `models/__init__.py` (0 lines). No models, views, serializers, or DRF routes exist.

## Expected / After
folio module has Folio/Charge/Payment models with append-only + void semantics, DRF ViewSets with permissions, serializers, and registered URLs under /api/folio/.

## Why
Rule 5 — Money is append-only; void only with reason/user/timestamp. Rule 12 — Tests protect folio posting, voids, checkout zero-balance. MVP scope Module C.

## Acceptance Criteria
- [ ] Folio, Charge, Payment models created with migrations
- [ ] Charge/Payment cannot be updated/deleted via API (append-only)
- [ ] Void endpoints exist with reason + user + timestamp
- [ ] DRF ViewSets + serializers + permissions
- [ ] URLs registered in config/urls.py
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/folio/models/folio.py`, `charge.py`, `payment.py`
- `backend/modules/folio/serializers/`
- `backend/modules/folio/views/`
- `backend/modules/folio/urls.py`
- `backend/modules/folio/services/`
- `backend/modules/folio/permissions/`
- `backend/modules/folio/migrations/`
- `backend/config/urls.py`

## Notes / Follow-ups
Money Rule 5 applies to Charge and Payment. Void reason `weather` is one allowed reason.

## Return to Pending Log
None.

## Completion Log
- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Implemented Module C (folio) — Folio, Charge, Payment models with append-only + void semantics (Rule 5). DRF ViewSets with permissions, serializers, services, and registered URLs under /api/folio/. Charge/Payment cannot be updated/deleted; void endpoints require reason + voided_by + timestamp.
- **Files changed**: backend/modules/folio/models/{folio,charge,payment}.py, backend/modules/folio/models/__init__.py, backend/modules/folio/migrations/0001_initial.py, backend/modules/folio/serializers/{folio,charge,payment}.py, backend/modules/folio/serializers/__init__.py, backend/modules/folio/permissions/folio.py, backend/modules/folio/permissions/__init__.py, backend/modules/folio/services/folio.py, backend/modules/folio/services/__init__.py, backend/modules/folio/views/folio.py, backend/modules/folio/views/__init__.py, backend/modules/folio/urls.py, backend/config/urls.py
- **Verification**: Code-only change, not executed (test DB setup pending per SUMMARY §3)
- **Change report**: work/changes/2026-11-02--19-folio-module-models-drff-apis.md
- **PLAN / SUMMARY**: updated yes