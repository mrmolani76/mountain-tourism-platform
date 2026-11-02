# Change Report: pos module — Product, Order, OrderLine models + DRF APIs

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 21

## 1. Summary
Implemented Module D (POS — Restaurants, huts, shop) — ProductCategory, Product, Order, OrderLine models with DRF ViewSets, serializers, permissions, services, and registered URLs under /api/pos/.

## 2. Before
`backend/modules/pos/` was completely empty: empty `__init__.py`, empty `urls.py`, 0-line `views/__init__.py`, `serializers/__init__.py`, `models/__init__.py`. No POS domain models or DRF APIs existed.

## 3. After
- **Models** (`backend/modules/pos/models/`): ProductCategory, Product (outlet+category, price, tax_rate), Order (open/paid/voided, outlet, staff, folio, total_amount), OrderLine (quantity, unit_price, tax_amount, is_voided with voided_at/reason/by).
- **Migrations** (`0001_initial.py`): Creates pos_product_category, pos_product, pos_order, pos_order_line tables; depends on core 0005 + folio 0001.
- **Serializers**: ProductCategorySerializer, ProductSerializer, ProductCreateSerializer, OrderSerializer, OrderCreateSerializer, OrderLineSerializer, OrderLineCreateSerializer.
- **ViewSets**: ProductCategoryViewSet, ProductViewSet, OrderViewSet, OrderLineViewSet. Cashier/front_desk/owner/manager can create orders. Manager+ for product/category write.
- **Permissions**: IsPosStaff (read), IsPosCashier (create orders).
- **Services**: place_order, add_order_line with validation.
- **URLs**: Registered under /api/pos/.

## 4. Reason
MVP scope Module D. POS is where cashiers take orders and charge to folio. OrderLine void = append-only void with reason (Rule 5).

## 5. Files Affected
- `backend/modules/pos/models/product_category.py` (new)
- `backend/modules/pos/models/product.py` (new)
- `backend/modules/pos/models/order.py` (new)
- `backend/modules/pos/models/order_line.py` (new)
- `backend/modules/pos/models/__init__.py` (new content)
- `backend/modules/pos/migrations/0001_initial.py` (new)
- `backend/modules/pos/serializers/product_category.py` (new)
- `backend/modules/pos/serializers/product.py` (new)
- `backend/modules/pos/serializers/order.py` (new)
- `backend/modules/pos/serializers/order_line.py` (new)
- `backend/modules/pos/serializers/__init__.py` (new content)
- `backend/modules/pos/permissions/pos.py` (new)
- `backend/modules/pos/permissions/__init__.py` (new content)
- `backend/modules/pos/services/order.py` (new)
- `backend/modules/pos/services/__init__.py` (new content)
- `backend/modules/pos/views/pos.py` (new)
- `backend/modules/pos/views/__init__.py` (new content)
- `backend/modules/pos/urls.py` (new content)
- `backend/config/urls.py` (added pos URL include)

## 6. Verification / Status
- [ ] Not executed — code-only change (test DB setup pending per SUMMARY §3)
- [ ] Migration file created but not applied
- [ ] Change report written: this file
- [ ] PLAN.md + SUMMARY.md updated (item 21 moved Todo → Completed)
