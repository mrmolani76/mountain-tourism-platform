---
title: pos module — Product, Order, OrderLine models + DRF APIs
id: 21
priority: high
status: completed
milestone: 2
module: D-pos
area: backend
effort: L
dependencies: 18
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# pos module — Product, Order, OrderLine models + DRF APIs

## What to do
Implement Module D (Restaurants, huts, shop) POS in `backend/modules/pos/`:
- Models: ProductCategory, Product, Order, OrderLine
- Serializers for all models
- ViewSets: ProductViewSet, OrderViewSet, OrderLineViewSet
- Permissions: role-based (cashier / front desk / owner)
- URLs: register pos routes in config/urls.py
- Services: place_order, add_order_line, void_order_line

## Current / Before
`backend/modules/pos/` has empty `__init__.py`, `urls.py` (empty urlpatterns), `views/__init__.py` (0 lines), `serializers/__init__.py` (0 lines), `models/__init__.py` (0 lines). No models, views, serializers, or DRF routes exist.

## Expected / After
pos module has Product/Order/OrderLine models, DRF ViewSets with permissions, serializers, and registered URLs under /api/pos/.

## Why
MVP scope Module D. POS is where cashiers take orders and charge to folio.

## Acceptance Criteria
- [ ] ProductCategory, Product, Order, OrderLine models created with migrations
- [ ] DRF ViewSets + serializers + permissions
- [ ] URLs registered in config/urls.py
- [ ] Change report written
- [ ] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `backend/modules/pos/models/`
- `backend/modules/pos/serializers/`
- `backend/modules/pos/views/`
- `backend/modules/pos/urls.py`
- `backend/modules/pos/services/`
- `backend/modules/pos/permissions/`
- `backend/modules/pos/migrations/`
- `backend/config/urls.py`

## Notes / Follow-ups
OrderLine void = append-only void with reason (Rule 5).

## Return to Pending Log
None.

## Completion Log
- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Implemented Module D (POS) — ProductCategory, Product, Order, OrderLine models with DRF ViewSets, serializers, permissions, services, and registered URLs under /api/pos/. OrderLine void = append-only void with reason (Rule 5).
- **Files changed**: backend/modules/pos/models/{product_category,product,order,order_line}.py, backend/modules/pos/models/__init__.py, backend/modules/pos/migrations/0001_initial.py, backend/modules/pos/serializers/{product_category,product,order,order_line}.py, backend/modules/pos/serializers/__init__.py, backend/modules/pos/permissions/pos.py, backend/modules/pos/permissions/__init__.py, backend/modules/pos/services/order.py, backend/modules/pos/services/__init__.py, backend/modules/pos/views/pos.py, backend/modules/pos/views/__init__.py, backend/modules/pos/urls.py, backend/config/urls.py
- **Verification**: Code-only change, not executed (test DB setup pending per SUMMARY §3)
- **Change report**: work/changes/2026-11-02--21-pos-module-models-drff-apis.md
- **PLAN / SUMMARY**: updated yes