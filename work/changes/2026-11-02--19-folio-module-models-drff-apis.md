# Change Report: folio module — Folio, Charge, Payment models + DRF APIs

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 19

## 1. Summary
Implemented Module C (Guest folio and checkout) — Folio, Charge, Payment models with append-only + void semantics, DRF ViewSets, serializers, permissions, services, and registered URLs under /api/folio/.

## 2. Before
`backend/modules/folio/` was completely empty: empty `__init__.py`, empty `urls.py`, 0-line `views/__init__.py`, `serializers/__init__.py`, `models/__init__.py`. No folio domain models or DRF APIs existed.

## 3. After
- **Models** (`backend/modules/folio/models/`): Folio (open/closed/voided status, total_amount), Charge (folio line, outlet, amount, tax, source, append-only), Payment (cash/card, amount, reference, append-only). All with voided_at/voided_reason/voided_by fields (Rule 5).
- **Migrations** (`0001_initial.py`): Creates folio_folio, folio_charge, folio_payment tables; depends on core 0005.
- **Serializers** (`backend/modules/folio/serializers/`): FolioSerializer, FolioCreateSerializer, FolioCloseSerializer, ChargeSerializer, ChargeCreateSerializer, ChargeVoidSerializer, PaymentSerializer, PaymentCreateSerializer, PaymentVoidSerializer.
- **Views/ViewSets** (`backend/modules/folio/views/`): FolioViewSet (open/close), ChargeViewSet (create + void action), PaymentViewSet (create + void action). PUT/PATCH/DELETE return 405 (append-only).
- **Permissions** (`backend/modules/folio/permissions/`): IsFolioStaff (read for all staff), IsFolioManager (close/void for owner/manager).
- **Services** (`backend/modules/folio/services/`): open_folio, close_folio, post_charge, record_payment, void_charge, void_payment, get_folio_balance.
- **URLs** (`backend/modules/folio/urls.py` + `backend/config/urls.py`): Registered under /api/folio/.

## 4. Reason
Rule 5 — Money is append-only; charge/payment void only with reason + user + timestamp. Rule 12 — Tests protect folio posting, voids, checkout zero-balance. MVP scope Module C.

## 5. Files Affected
- `backend/modules/folio/models/folio.py` (new)
- `backend/modules/folio/models/charge.py` (new)
- `backend/modules/folio/models/payment.py` (new)
- `backend/modules/folio/models/__init__.py` (new content)
- `backend/modules/folio/migrations/0001_initial.py` (new)
- `backend/modules/folio/serializers/folio.py` (new)
- `backend/modules/folio/serializers/charge.py` (new)
- `backend/modules/folio/serializers/payment.py` (new)
- `backend/modules/folio/serializers/__init__.py` (new content)
- `backend/modules/folio/permissions/folio.py` (new)
- `backend/modules/folio/permissions/__init__.py` (new content)
- `backend/modules/folio/services/folio.py` (new)
- `backend/modules/folio/services/__init__.py` (new content)
- `backend/modules/folio/views/folio.py` (new)
- `backend/modules/folio/views/__init__.py` (new content)
- `backend/modules/folio/urls.py` (new content)
- `backend/config/urls.py` (added folio URL include)

## 6. Verification / Status
- [ ] Not executed — code-only change (test DB setup pending per SUMMARY §3)
- [ ] Migration file created but not applied
- [ ] Change report written: this file
- [ ] PLAN.md + SUMMARY.md updated (item 19 moved Todo → Completed)
