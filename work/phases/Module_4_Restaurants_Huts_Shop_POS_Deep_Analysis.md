# Module 4 — Restaurants, Huts & Shop
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module D, page 4  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Prepared:** 3 November 2026  
**Purpose:** Convert the client's Module D requirements into an implementation-ready work document, while separating explicit PDF requirements from recommended design decisions and current repository audit findings.

---

## 1. Module objective

Module 4 manages product/menu sales and ordering across the complex's restaurants, huts and shop.

It must support outlets at different altitudes, configurable products and categories, seasonal menu visibility, tablet-based order entry, meal-inclusion checks for hotel guests, packed lunches, printable kitchen tickets and receipts, payment now or charge to a guest room, and cashier shift opening/closing with cash count.

The module must integrate with:
- **Module A — Core & SaaS:** complex tenant, buildings, outlets, staff, role and outlet access, seasons, tax settings.
- **Module B — Hotel, Chalets & Huts:** guest stay, room/guest lookup and board plan.
- **Module C — Guest Folio & Checkout:** room charges, payments, tax, voids, append-only money rules and folio balance.

This module is not complete when product and order CRUD endpoints merely exist. The client's acceptance scenario must work end to end: a half-board guest's dinner is recorded as included and a bottle of wine at the same table is charged to the guest's room.

## 2. Source requirements vs design recommendations

This document uses three labels:

- **PDF requirement:** Explicitly stated in the client's MVP scope.
- **Audit finding:** Reported in the 3 November 2026 project audit.
- **Recommendation / confirmation:** A practical implementation proposal or a question that the PDF does not answer. Do not treat it as a contractual requirement until agreed.

### 2.1 Explicit requirements from the PDF

| Area | Client requirement |
|---|---|
| Outlets | Outlets can exist at any altitude: valley restaurant, mid-station self-service, summit restaurant, hut kitchen, bar and gear shop |
| Products | Products have categories, price and tax |
| Seasonal menus | Menus can be shown or hidden by season |
| Order screen | Tablet order screen |
| Order details | A note per item and a table number |
| Board check | When the cashier picks a hotel guest, the screen shows whether the meal is included in that guest's board plan |
| Packed lunches | Ordered the evening before |
| Kitchen preparation | A printed packed-lunch list is available for the kitchen in the morning |
| Kitchen ticket | Printable kitchen ticket |
| Receipt | Printable receipt, browser print, 80 mm |
| Payment choices | Pay now by cash or card, or charge to room |
| Cashier shift | Open and close cashier shift with cash count |
| Acceptance test | A half-board guest's dinner is recorded as included, while a bottle of wine at the same table is charged to the room |

### 2.2 Important boundaries

The supplied PDF does **not** explicitly define:
- stock/inventory quantity tracking, purchase orders or supplier management;
- barcode scanning or SKU format;
- discounts, coupons, loyalty points or promotions;
- online payment gateway integration;
- kitchen display system or kitchen status workflow;
- table layout/floor-plan management;
- split payments at the POS (Module C allows multiple payments on a folio, but the POS flow must be designed consistently);
- refunds or a detailed POS order cancellation process;
- recipe/ingredient management;
- a delivery/takeaway platform.

Do not silently add these to the MVP. Keep them out of the required scope unless the client approves them.

## 3. Outlet model and outlet configuration

### 3.1 Outlets in the complex

The PDF gives examples of outlets at different altitudes:

| Example outlet | Example location |
|---|---|
| Valley restaurant | Valley station |
| Mid-station self-service | Mid station |
| Summit restaurant | Summit station |
| Hut kitchen | Mountain hut |
| Bar | Any configured building |
| Gear shop | Valley station or another configured building |

Outlets and buildings are already core concepts in Module A. Module D should use the existing outlet records rather than create a second outlet system.

### 3.2 Required behaviour

- Each outlet must be identifiable when a cashier creates an order.
- Each outlet has products/menu availability appropriate to its operation.
- Sales must retain their outlet identity so owner reports can distinguish outlet revenue.
- Product and menu visibility must respect the configured season.
- Staff access should be constrained by the outlet access assigned in Module A.

### 3.3 Recommended configuration fields

These are design proposals, not a complete field list mandated by the PDF:

| Field | Purpose |
|---|---|
| `outlet` | Existing Module A outlet reference |
| `name` | Display name |
| `category` | Optional outlet classification if not already represented in Module A |
| `is_active` | Whether the outlet can take orders |
| `timezone` | Prefer tenant setting from Module A rather than duplicating it |
| `receipt_footer` | Optional printed receipt text; confirm if needed |

Do not duplicate the existing Building, Outlet, Season, Staff or tax configuration models.

## 4. Product Categories

The PDF requires products to have categories. It does not define a fixed category list or exact category fields.

Illustrative category examples:
- Hot drinks
- Cold drinks
- Breakfast
- Main course
- Snacks
- Desserts
- Wine and beverages
- Shop merchandise

These are examples only. The complex should be able to configure categories appropriate to its outlets.

### Required capabilities

- List product categories.
- Create a category.
- Update category details.
- Enable/disable a category for new product selection.
- Associate products with categories.
- Filter products by category in the POS.

### Recommended fields

| Field | Purpose | Status |
|---|---|---|
| `name` | Category name | Needed to represent category |
| `description` | Optional explanation | Recommendation |
| `is_active` | Hide category from new POS selection | Recommendation |
| `sort_order` | POS display order | Recommendation |

Category deletion should not break historical orders. Prefer deactivation or protect categories referenced by products/orders. This is a data-integrity recommendation.

## 5. Product Management

### 5.1 Explicit client requirement

The PDF requires products with:
- category;
- price;
- tax.

The PDF does not explicitly require SKU, barcode, stock quantity, supplier, cost price, variants or images.

### 5.2 Expected capabilities

- Create and edit products.
- Assign each product to a category.
- Set its selling price.
- Configure or select its tax treatment.
- Make the product available to the relevant outlet/menu.
- Preserve historical order details if the product name, price or tax is changed later.

### 5.3 Recommended product data

| Field | Purpose | Requirement status |
|---|---|---|
| `name` | Product display name | Necessary product identity |
| `category` | Category relationship | PDF requirement |
| `selling_price` | Current selling price | PDF requirement |
| `tax_rate` or tax reference | Applied tax | PDF requirement |
| `description` | Optional details | Recommendation |
| `is_active` | Prevent new selection | Recommendation |
| `sku` | Internal code | Optional; confirm before adding |
| `barcode` | Scan identifier | Not in PDF |
| `image` | Product thumbnail | Not in PDF |
| `stock_quantity` | Inventory quantity | Not in PDF; out of MVP unless approved |
| `cost_price` | Margin analysis | Not in PDF |

### 5.4 Price and tax snapshot

When an order is placed, store the product name/description, unit price and tax information used for that order item. This is a recommended historical integrity measure: a later product price change must not rewrite a completed order or already posted folio charge.

Money must follow the global technical rule in the PDF: use integer minor units (for example, cents) rather than floating-point values. The project audit notes that existing folio amounts use `DecimalField`; reconcile the cross-module implementation before integrating POS money with Folio.

## 6. Seasonal Menu Management

The PDF explicitly says menus can be shown or hidden by season.

### Required behaviour

- Associate menu/product availability with the tenant's existing seasons.
- Allow a menu or its items to be visible in one season and hidden in another.
- Ensure the POS shows only items available for the selected outlet and active season.
- Preserve old order data when seasons change.

### Recommended design options

Choose one consistent model after reviewing the current repository:

**Option A — Seasonal availability on product/outlet mapping**
- Product is mapped to an outlet.
- The mapping records which seasons the product is available in.

**Option B — Menu and menu items**
- A menu belongs to an outlet and season.
- Menu items link products and define visibility/availability.

The PDF uses the word “menus” but does not prescribe the schema. If the complex requires multiple named menus per outlet/season (for example breakfast menu and dinner menu), Option B may fit better. Confirm this with the client before committing the data model.

### Seasonal validation

- A product hidden for the current season must not appear for new POS orders.
- Historical orders must remain readable even when a product/menu is later hidden.
- Date and season resolution must use the tenant's configured timezone and season dates from Module A.

## 7. POS Order Management

### 7.1 Order screen

The PDF requires a tablet order screen.

At minimum, the screen must allow the cashier to:
- work in the context of an outlet;
- select available products;
- view product category, price and tax;
- add an item to an order;
- enter a note per order item;
- record a table number;
- identify whether the order belongs to a hotel guest;
- choose the relevant payment/room-charge path.

The PDF does not prescribe a specific POS UI layout, table grid, touch-screen library or device framework.

### 7.2 Order and order-item concepts

Recommended domain concepts:

**POS Order**
- tenant context is supplied by the established tenant database/routing model;
- outlet;
- cashier/staff member;
- table number;
- order date/time;
- order items;
- optional linked guest/stay/folio reference when a hotel guest is selected;
- payment or folio-charge outcome.

**POS Order Item**
- product reference for traceability;
- product name snapshot;
- quantity;
- unit price snapshot;
- tax snapshot;
- item note;
- line total.

These are proposed model responsibilities. Inspect existing POS models before creating or renaming tables.

### 7.3 Item notes

The PDF explicitly requires a note per item, not only one note for the entire order.

Examples:
- “No onions”
- “Extra hot”
- “Pack separately”

These examples illustrate use; the actual note content is free text unless the client asks for structured modifiers.

### 7.4 Table number

The order must record a table number. The PDF does not require table-management or table-reservation functionality. A simple table-number field is sufficient for the written scope unless clarified otherwise.

## 8. Guest selection and Board Plan Check

This is a critical business rule and a key integration with Module B.

### 8.1 PDF requirement

When the cashier picks a hotel guest, the screen must show whether the meal is included in that guest's board plan.

### 8.2 Expected cashier workflow

1. Cashier starts an order for the current outlet.
2. Cashier selects or looks up the hotel guest (the implementation should resolve the active stay and its board plan).
3. POS displays the meal inclusion result for the relevant meal/service.
4. Cashier adds the guest's order items.
5. Included meal items are recorded as included and must not be sold/charged again.
6. Additional items that are not included (the PDF's example is a bottle of wine) can be charged to the room.

### 8.3 Half-board acceptance example

A guest has a Half Board stay:
- Dinner is included.
- A bottle of wine is additional.

Expected outcome:
- Dinner is recorded as included.
- Dinner is not posted as a duplicate charge to the folio.
- Wine is charged to the guest's room/folio.
- Order history still shows what was served.

### 8.4 Critical distinction: meal entitlement vs product

A board plan defines meal entitlement; it does not automatically mean every product on the order is free.

The POS must distinguish:
- an included meal/meal entitlement;
- products included as part of that meal according to the complex's agreed rules;
- additional products such as wine.

The PDF does not provide a full food entitlement matrix, exact breakfast/lunch/dinner schedule, eligible products, children rules or arrival/departure day policy. Those need client confirmation. Do not implement a simplistic rule such as “all items in a dinner order are free” because that would incorrectly include extras.

### 8.5 Recommended data/logic responsibilities

- Read the guest's active stay and selected board plan from Module B.
- Evaluate the current meal entitlement using an agreed meal type and service date/time.
- Display the result clearly before finalising payment.
- Record the inclusion decision against the order/order items for audit.
- Do not create a second folio charge for an included meal.
- Route additional room-charge items to Module C.

The precise entitlement engine and data model are recommendations; the PDF only specifies that the cashier screen must show whether the meal is included.

## 9. Packed Lunch Management

### 9.1 Explicit requirement

The PDF specifies:
- packed lunches for hikers and skiers;
- orders are placed the evening before;
- a printed list is available for the kitchen in the morning.

### 9.2 Expected workflow

1. Staff/cashier records a packed-lunch order on the prior evening.
2. Order captures the required quantity and the products/items requested.
3. The order is associated with the relevant outlet/kitchen.
4. The next morning the kitchen can print a consolidated packed-lunch preparation list.
5. Staff prepare/hand over the packed lunches.

The PDF does not prescribe the exact cut-off time, guest pickup time, whether orders are individually named or consolidated, or the payment timing. Confirm these details.

### 9.3 Recommended packed-lunch data

| Field | Purpose |
|---|---|
| `outlet` | Kitchen responsible for preparation |
| `service_date` | Date the packed lunch is needed |
| `ordered_at` | When it was requested |
| `guest/stay` | Optional link where ordered for a hotel guest |
| `quantity` | Number of packed lunches |
| `items` | Requested products/items |
| `notes` | Dietary/preparation notes if client wants them |
| `status` | Operational status; exact states to be agreed |

The status workflow is a recommendation, not specified in the PDF.

### 9.4 Kitchen morning printout

The printout should make it easy for kitchen staff to prepare the morning's orders. It should at least show the information needed to identify the requested packed lunches and quantities.

A consolidated list by service date/outlet is a sensible implementation. The PDF requires a printed list but does not prescribe its exact layout.

## 10. Kitchen Tickets and Receipts

### 10.1 Kitchen ticket

The PDF requires a printable kitchen ticket.

Recommended ticket content:
- outlet name;
- order/ticket number;
- order time;
- table number;
- item names and quantities;
- per-item notes;
- relevant guest/order reference where necessary.

The item notes and table number are explicit PDF fields; the other ticket fields are practical print-layout recommendations.

### 10.2 Customer receipt

The PDF requires a printable receipt using browser print, sized for an 80 mm printer.

The receipt should be readable on a thermal printer and show enough information to identify the transaction and its products/tax/payment. Exact mandatory receipt fields and local fiscal requirements are not specified in the PDF and must be confirmed for the deployment jurisdiction.

### 10.3 Technical boundary

- Browser print is in scope.
- A paid print service or proprietary hardware integration is not specified.
- Test actual 80 mm print layout: wrapping, totals, tax, long product names and item notes.
- Do not claim PDF invoice generation here unless agreed; Module C separately covers the guest folio invoice.

## 11. Payment and Charge-to-Room Paths

The PDF offers two payment paths:

1. Pay now by cash or card.
2. Charge to room.

### 11.1 Pay now

Cashier records cash or card payment for the order. The project PDF says payments are recorded by staff and that there is no payment gateway in the MVP (Module C). Do not add online gateway processing to this module.

### 11.2 Charge to room

For a hotel guest, eligible order items can be posted to that guest's folio through Module C.

Expected flow:
1. Cashier selects the hotel guest/active stay.
2. POS resolves the linked folio.
3. Board inclusion is checked.
4. Included items are recorded as included, not charged again.
5. Additional items marked for room charge are sent to the folio charge service.
6. The folio charge retains its outlet, tax and source information in accordance with Module C.

### 11.3 Billing source and reports

The PDF's core accounting design states:
- outlet revenue comes from charge lines;
- cash reporting comes from payment records;
- each charge records outlet, tax and source;
- charges/payments are not edited or deleted; they are voided with reason, user and timestamp.

Therefore, POS must not become a separate conflicting source of financial truth. Integrate with the shared Folio/Charge/Payment services for room charges and payments, while keeping order operational details in POS.

### 11.4 Payment gateway

No gateway is required in the MVP. Cash and card are recorded by staff. Do not integrate Stripe, Razorpay, online payment collection or terminal APIs unless the client separately approves them.

## 12. Cashier Shift Management

The PDF explicitly requires the cashier to open and close a shift with a cash count.

### 12.1 Minimum shift operations

- Open a cashier shift.
- Record opening cash count.
- Associate POS cash activity with the active shift.
- Close the shift.
- Record closing cash count.

### 12.2 Recommended reconciliation

A useful shift close summary may compare:
- opening cash;
- cash received during shift;
- cash refunds/void adjustments, if supported by the agreed policy;
- expected cash;
- counted closing cash;
- cash difference.

The PDF explicitly requires opening/closing and a cash count, but does not specify reconciliation formulas, denomination counting, manager approval or shift handover. Confirm the desired close report.

### 12.3 Important separation

A cashier shift is an operational cash-control record. It should not replace the central Payment records from Module C. The shift should reference the payments that belong to it, so the financial record remains consistent.

## 13. Suggested data model

The names below are proposals for organising the code. They are not mandated by the client PDF and must be checked against existing repository models before making migrations.

| Proposed model | Responsibility |
|---|---|
| `ProductCategory` | Configurable product grouping |
| `Product` | Product identity and current price/tax |
| `OutletProduct` or `MenuItem` | Product availability for an outlet |
| `SeasonalMenu` / season mapping | Outlet menu visibility by season |
| `POSOrder` | Order header, outlet, cashier, table and guest context |
| `POSOrderItem` | Product, quantity, price/tax snapshot and per-item note |
| `PackedLunchOrder` | Advance packed-lunch request |
| `PackedLunchOrderItem` | Items and quantities for packed lunch |
| `CashierShift` | Open/close and counted cash |
| `POSPaymentReference` | Optional link to central payment records, if needed |

### Relationship overview

```text
Tenant database
 ├── Building (Module A)
 │    └── Outlet (Module A)
 │         ├── OutletProduct / SeasonalMenu
 │         │    └── Product ── ProductCategory
 │         ├── POSOrder
 │         │    └── POSOrderItem
 │         │         ├── Product reference + snapshots
 │         │         └── optional per-item note
 │         ├── PackedLunchOrder
 │         │    └── PackedLunchOrderItem
 │         └── CashierShift
 │
 ├── Season (Module A)
 ├── Stay / BoardPlan (Module B)
 └── Folio / Charge / Payment (Module C)
      └── POS room-charge and payment integration
```

Do not create duplicate `Outlet`, `Season`, `Stay`, `BoardPlan`, `Folio`, `Charge` or `Payment` models when the existing modules already own them.

## 14. Suggested API capability map

The PDF does not define API URL paths. This is a proposed DRF capability map, not a claim that these routes exist.

| Area | Suggested API capability |
|---|---|
| Categories | List, create, retrieve, update, deactivate |
| Products | List, create, retrieve, update, deactivate |
| Outlet product/menu | List products available at outlet and season; configure visibility |
| POS order | Create order, retrieve order, list/search orders |
| Order items | Add/update before finalisation; record item note and quantity |
| Guest lookup | Search/select a current hotel guest/stay |
| Board check | Return whether the selected meal is included for the guest |
| Room charge | Send eligible extra items to Module C folio charge service |
| Pay now | Record cash/card outcome using the shared payment rules |
| Packed lunch | Create and retrieve next-day packed-lunch orders |
| Kitchen list | Retrieve/print packed-lunch preparation list by outlet/service date |
| Kitchen ticket | Render printable order ticket |
| Receipt | Render browser-print receipt for 80 mm paper |
| Cashier shift | Open shift, view active shift, close shift with count |

### API and security rules

- Use the project's existing authentication class and permissions.
- The 3 November audit reports a critical authentication configuration problem on B–I domain ViewSets. Fix this shared issue before trusting POS APIs.
- Check staff outlet access on the server.
- Keep business logic in service functions, not only in views/serializers.
- Validate product visibility for the selected outlet and season.
- Use integer minor units for money in line with the PDF's technical rule.
- Do not accept `voided_by` or other actor identity from client request bodies; bind the actor from the authenticated request user through the shared Folio service.
- Keep money append-only and use shared void operations rather than editing/deleting posted charges or payments.

## 15. Frontend screens

Suggested responsive/tablet-friendly screens:

1. Product Categories list and form.
2. Products list and form.
3. Outlet product/menu configuration.
4. Seasonal menu visibility screen.
5. POS order-entry screen for tablets.
6. Guest search/selection panel.
7. Board-plan inclusion indicator.
8. Order review and payment choice.
9. Packed-lunch order entry.
10. Morning packed-lunch kitchen list/print view.
11. Kitchen ticket print view.
12. 80 mm customer receipt print view.
13. Cashier shift open screen.
14. Active shift summary.
15. Shift close and cash-count screen.

These screens are implementation recommendations. The PDF specifies functions and print outputs, not screen wireframes or a component library.

## 16. Existing repository audit findings

The 3 November 2026 Project Audit Report says:

| Item | Audit finding |
|---|---|
| POS models | Present |
| POS ViewSets | Present, but thin CRUD/business depth |
| POS services | Thin |
| Order-to-Folio relationship | An order can link to a folio FK |
| Post-to-Folio charge service | Missing |
| Board inclusion check | Missing |
| Frontend POS workflow | Placeholder; not a working mountain POS desk |
| Domain API authentication | Critical defect affects POS and other B–I ViewSets |

The audit also says the frontend still contains template/EQMS-style customers, invoices and products, while the mountain module pages are placeholders.

**Do not recreate all POS models blindly.** Inspect the existing `backend/modules/pos/` code, migrations, serializers, views, services and tests. Extend existing models and preserve migration immutability.

## 17. Recommended implementation sequence

Work one step at a time. Each step should include code, validation and automated tests before moving on.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect current POS implementation; fix/verify domain API authentication | Authenticated staff can access authorised POS APIs; unauthorised staff are rejected |
| 1 | Product Category management | Category CRUD and validation |
| 2 | Product management | Product with category, price and tax; history-safe order snapshots |
| 3 | Outlet product mapping | Correct product list for each outlet |
| 4 | Seasonal menu visibility | Products shown/hidden according to current tenant season |
| 5 | POS order and order-item flow | Tablet-sized order screen, table number and per-item note |
| 6 | Hotel guest selection | Cashier can select guest/current stay |
| 7 | Board inclusion check | Half-board dinner recognised as included |
| 8 | Room-charge integration | Additional product such as wine posts to correct folio |
| 9 | Pay-now cash/card | Staff can record payment without gateway |
| 10 | Kitchen ticket and receipt | Browser-print output; receipt fits 80 mm paper |
| 11 | Packed lunch workflow | Evening-before order and next-morning printable kitchen list |
| 12 | Cashier shift | Open, record opening count, close and record closing count |
| 13 | End-to-end acceptance tests | Client's Half Board dinner + wine scenario passes |
| 14 | Regression and reporting checks | Outlet, tax, charge and payment totals remain consistent |

## 18. Test scenarios

### 18.1 Product and menu tests

- A product can be associated with a category.
- Product price and tax are validated.
- An inactive product cannot be selected for a new order.
- A product hidden for the active season is not offered at the POS.
- A historical order still displays its original product/price/tax snapshot after the product is updated.

### 18.2 Outlet access tests

- Staff can use only outlets they are authorised to access.
- Product availability is scoped to the selected outlet.
- A user cannot submit an order for an outlet outside their assigned access.

### 18.3 Order tests

- An order can contain multiple items.
- Each item can have its own note.
- Table number is stored.
- Order items have correct quantity, price and tax snapshots.
- Finalised money records cannot be silently edited/deleted.

### 18.4 Board plan tests

- A hotel guest can be selected from an active stay.
- POS shows the relevant meal-inclusion status.
- An included meal is recorded as included without a duplicate folio charge.
- An extra item such as wine is charged separately when the room-charge path is selected.
- A guest without an eligible board plan is not incorrectly shown as entitled to a meal.

### 18.5 Folio and payment tests

- Charge-to-room uses the correct guest folio.
- Each posted charge retains outlet, tax and source information.
- Cash/card paid-now activity is recorded consistently with Module C.
- No payment gateway is called.
- Void operations follow Module C's reason/actor/timestamp rules.

### 18.6 Packed-lunch tests

- Orders for a service date can be entered the evening before.
- The kitchen list shows the relevant orders the next morning.
- The print list is scoped to the correct outlet and service date.
- Item quantities/notes are not lost in print output.

### 18.7 Cashier shift tests

- Cashier can open a shift and record opening cash.
- The system associates relevant cash transactions with the active shift.
- Shift cannot be closed without the required closing count.
- Closing summary reconciles according to the rule agreed with the client.

### 18.8 Client acceptance scenario

**Given:** A hotel guest is staying on Half Board.

**When:**
1. Cashier selects the hotel guest.
2. POS checks the guest's board plan.
3. Cashier records the guest's dinner.
4. The same table orders a bottle of wine.
5. Cashier charges the additional wine to the guest's room.

**Then:**
- POS indicates that dinner is included.
- Dinner is recorded as included and is not charged again.
- Wine is posted as a separate charge to the correct guest folio.
- The order retains its outlet and item details.
- Outlet revenue and payment reporting follow the shared charge/payment design.

This is the primary “Done when” check from the client's PDF, with the expected technical assertions made explicit.

## 19. Client questions to resolve

The PDF leaves these decisions open. Obtain answers before making them hard-coded business rules:

1. Can the same product be sold by multiple outlets at different prices?
2. Is a product shared across all outlets by default, or must every outlet explicitly enable it?
3. Does seasonal visibility apply to a whole menu, individual products, or both?
4. Does the client want SKU/barcode now, even though it is not in the MVP PDF?
5. Is stock quantity tracking required for the gear shop or restaurant products? It is not specified in this module's PDF scope.
6. What exactly counts as an included meal, and which product categories/items are excluded (for example beverages/wine)?
7. How does the cashier choose breakfast, lunch or dinner entitlement?
8. What happens if a hotel guest's stay or folio cannot be found?
9. Can one POS order be split between pay-now and charge-to-room?
10. What packed-lunch order cut-off time applies the previous evening?
11. Should packed-lunch lists be grouped by guest, item or both?
12. What information and tax/legal fields must appear on the 80 mm receipt?
13. What is the cashier shift reconciliation rule and who may close a shift?
14. What is the policy for cancelling an order after a payment or folio charge has been recorded?

## 20. Definition of done

Module 4 should not be marked complete until all PDF requirements work together:

- Outlets at different buildings/altitudes can sell their configured products.
- Products have categories, prices and taxes.
- Seasonal menus can be shown/hidden by season.
- Staff can enter orders on a tablet-sized POS screen.
- Each order item supports a note and the order records a table number.
- Selecting a hotel guest triggers a visible board-plan meal-inclusion check.
- Packed lunches can be ordered the evening before.
- The kitchen can print the next-morning packed-lunch list.
- Kitchen tickets can be printed.
- Customer receipts can be browser-printed on 80 mm paper.
- Orders can be paid now by cash/card or charged to a guest room.
- Cashier shifts can be opened/closed with cash count.
- The Half Board dinner + wine acceptance scenario passes.
- Automated tests cover product/menu rules, outlet access, board inclusion, folio integration and shift handling.

## 21. Source references

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module D, page 4. This is the source for outlets at any altitude, products/categories/price/tax, seasonal menus, tablet order screen, item notes, table number, board check, packed lunches, print outputs, payment paths, cashier shifts and the acceptance test.
- **Client MVP PDF:** Core concepts and money-flow rules, pages 2–3; technical rules, page 7. These define the shared Outlet, Season, Folio, Charge and Payment concepts, outlet revenue reporting, append-only money and integer minor units.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, sections 3, 5 and 8. This reports existing POS models/thin services, the existing order-to-folio FK, missing post-to-folio service, missing board inclusion check, frontend placeholders and the recommended authentication fix.

---

**Implementation principle:** First inspect the existing POS code and fix the shared domain authentication issue. Then build Product Category → Product → Outlet/Season availability → POS order flow → Board Check → Folio integration → print outputs → packed lunch → cashier shift. Do not add stock management, SKU/barcode or payment gateway functionality to the MVP without client approval.
