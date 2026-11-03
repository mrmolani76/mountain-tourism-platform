# Module 5 — Equipment Rental
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module E, page 4  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Translate the client's Equipment Rental scope into an implementation-ready work specification, while distinguishing explicit PDF requirements, current repository findings and recommended design decisions.

---

## 1. Module objective

The Equipment Rental module manages physical equipment that the mountain complex rents to hotel guests and walk-in customers.

It must let the complex define the types of equipment it rents, maintain individual rentable items, set a per-day price by season, record a deposit, rent equipment out, process returns, record damage and a damage fee, and identify equipment that is currently out or overdue.

The module must enforce two explicit business outcomes from the client PDF:

1. The same physical equipment item must not be rented to two customers at the same time.
2. A late return must add the extra rental day to the bill.

This is an equipment-item tracking workflow, not just a CRUD screen for rental transactions. The platform needs to know which individual item (identified by its item code and size) is currently with which customer and when it is due back.

## 2. Source requirements and scope boundaries

### 2.1 Explicit requirements from the client PDF

| Area | Requirement stated in the PDF |
|---|---|
| Equipment types | The complex defines the types it rents |
| Examples | Skis, snowboards, boots, helmets, poles, sleds, bikes, e-bikes, hiking poles and similar items |
| Individual item | Each item has a size and an item code |
| Pricing | Price per day by season |
| Deposit | A deposit is recorded for the rental |
| Customer | Equipment can be rented to a hotel guest or a walk-in customer |
| Checkout | Record equipment being rented out |
| Return | Record returned equipment |
| Damage | Mark an item damaged and apply a fee |
| Current rentals | List everything currently out |
| Overdue | Highlight overdue items |
| No double rental | One item cannot be rented twice at the same time |
| Late return | A late return adds the extra day to the bill |

### 2.2 Out of scope unless the client approves it

The PDF does not explicitly require the following features:

- advance online rental reservations;
- customer self-service booking;
- equipment maintenance/workshop management;
- barcode or QR scanning;
- RFID integration;
- stock purchasing, suppliers or purchase orders;
- depreciation or asset accounting;
- automated damage inspection with photos;
- waiver/e-signature workflow;
- rental insurance;
- discounts, coupons or membership pricing;
- delivery or collection scheduling;
- payment gateway integration.

The PDF requires an item code, but does not say that the code must be a barcode or QR code. Do not add scanning hardware/workflows without approval.

## 3. Key domain concepts

### 3.1 Equipment type

An Equipment Type is a category of equipment the complex offers for rental.

Examples from the PDF include:
- skis;
- snowboards;
- boots;
- helmets;
- poles;
- sleds;
- bikes;
- e-bikes;
- hiking poles.

The list is illustrative and can be configured by the complex. Do not hard-code only these examples.

### 3.2 Individual equipment item

An Equipment Item is one physical unit available for rental.

Every individual item must have:
- a type;
- a size;
- an item code.

For example, “Ski” is the type, while “SKI-001 / 170 cm” is one physical item. If a complex owns 25 pairs of skis, it needs 25 separately identifiable items so the system can prevent one physical pair from being rented twice.

The item-level record is the key to availability and conflict validation.

### 3.3 Rental transaction

A Rental represents the period during which one or more individual items are issued to a customer.

The PDF speaks about “one rented item” in the core concepts and requires each item to be controlled for overlapping rental times. The simplest reliable implementation is to track each issued physical item separately, even if the staff UI allows multiple items to be checked out in one customer transaction.

Whether a single rental transaction may contain multiple items should be confirmed. The system design can support a rental header with rental-item rows, but this is a recommended structure rather than a schema prescribed by the PDF.

## 4. Equipment Type Management

### 4.1 Required behaviour

The complex must be able to define the types of equipment it rents. The system should not require a developer to add a new equipment type whenever a complex starts renting a new kind of item.

Expected capabilities:
- list equipment types;
- create an equipment type;
- update its name/details;
- make a type unavailable for new rentals when no longer offered;
- associate individual equipment items with a type.

### 4.2 Recommended fields

The PDF requires configurable equipment types but does not list exact fields. Suggested fields:

| Field | Purpose | Status |
|---|---|---|
| `name` | Equipment type name | Needed to identify the type |
| `description` | Optional details | Recommendation |
| `is_active` | Show/hide type for new rental operations | Recommendation |
| `sort_order` | Staff-screen ordering | Recommendation |

Do not delete a type if historical rental records depend on it. Deactivation is safer for reporting and history.

## 5. Individual Equipment Inventory

### 5.1 Required item information

The PDF explicitly requires each item to have:
- size;
- item code.

Each item must also be associated with its equipment type so that staff can identify what is being rented.

### 5.2 Expected capabilities

- Add individual equipment items.
- Assign an equipment type.
- Store size.
- Store a unique item code within the appropriate complex.
- View the current item status/availability.
- Identify the customer/rental holding the item.
- Prevent conflicting rentals of the same item.
- Retain the item's rental history.

### 5.3 Recommended fields

| Field | Purpose | Status |
|---|---|---|
| `equipment_type` | Type/category reference | Required by domain model |
| `item_code` | Physical item identifier | PDF requirement |
| `size` | Size of the physical item | PDF requirement |
| `description` | Optional detail | Recommendation |
| `is_active` | Whether item can be issued | Recommendation |
| `operational_status` | Available / out / damaged etc. | Proposed; align with rental lifecycle |

The exact uniqueness scope for item code (per tenant, per outlet or global) is not stated. Since each complex is a tenant, a unique code within the tenant is a practical default; confirm if the client expects global uniqueness.

### 5.4 Inventory is individual-item based

Do not represent rental inventory only as a quantity such as “10 skis available.” Quantity totals cannot identify which physical item is with a customer and cannot reliably prevent the same unit from being rented twice.

The current project audit specifically identifies the absence of an inventory/SKU pool as a gap. The implementation needs an individual item inventory layer.

## 6. Seasonal Rental Pricing

### 6.1 Explicit requirement

The PDF states that each item has a **price per day by season**.

The platform's Season concept already belongs to Module A. Rental prices should reference the existing tenant season data rather than creating a separate seasonal calendar.

### 6.2 Required behaviour

- Define a daily rental price for the relevant equipment type/item and season.
- Resolve the correct season and price for the rental period.
- Calculate the expected rental fee from the applicable daily price and billable rental days.
- Retain the price used for an issued rental so that later price changes do not rewrite an existing rental's historic financial values.

The last point is a recommended historical-data practice. The PDF does not prescribe snapshot implementation.

### 6.3 Pricing design decision

The PDF says “each item” has a price per day by season, but it does not clarify whether the price is configured:
- separately for every physical item; or
- once for an equipment type and inherited by its individual items, with an optional item override.

A maintainable proposal is:
- default daily price by Equipment Type + Season;
- optionally allow an item-specific override only if the client needs it.

Confirm this before finalising the schema. Do not build two pricing systems without a business need.

### 6.4 Rental date spans and daily price resolution

A rental may span dates that cross a season boundary. The PDF does not specify how that case is priced.

A recommended approach is to resolve each billable rental day against the season applicable to that date and sum the daily prices. Confirm this with the client before implementing cross-season billing.

## 7. Deposit Management

### 7.1 Explicit requirement

The PDF requires a deposit to be recorded for a rental.

### 7.2 What the system must capture

At minimum, the rental workflow must retain the deposit amount associated with the equipment rental.

The client PDF does not explain:
- whether the deposit is mandatory for every item;
- whether the amount is configured by equipment type or entered by staff;
- whether it is collected in cash/card or recorded only;
- whether it is refundable;
- when it is returned to the customer;
- how damage or late fees are deducted from it;
- whether deposit collection is a payment record, a liability record or another accounting entry.

These are important financial decisions. Do not assume that the deposit is rental revenue or automatically post it as a normal sale charge. Agree the deposit accounting and refund treatment with the client and align it with Module C's money records.

### 7.3 Recommended data handling

Keep separate concepts for:
- deposit amount required/expected;
- deposit amount actually received;
- deposit return/refund, if applicable;
- any agreed deduction, such as damage or late fee.

This separation is a recommendation. The exact financial workflow requires client confirmation.

## 8. Customer Types: Hotel Guest and Walk-in

The PDF explicitly supports two customer types.

### 8.1 Hotel guest

For a hotel guest:
- staff should be able to identify/select the guest;
- the rental should be associated with the guest's stay where available;
- rental charges should be capable of integrating with the guest Folio under Module C;
- the rental record must preserve which guest/stay received the item.

The cross-module core concept states that charges record their outlet, tax and source; rental is one of the charge sources. The platform's folio design should therefore be reused rather than creating a separate room-billing ledger.

### 8.2 Walk-in customer

The PDF also allows rentals for walk-in customers.

The system must be able to create a rental without requiring a hotel stay or room number.

The PDF does not prescribe mandatory walk-in identity fields such as full name, phone number or ID document. Determine the minimum data needed for the client's operational and legal needs.

### 8.3 Customer selection rule

A hotel guest rental and a walk-in rental should be distinguishable in the rental record. Do not make a hotel guest link mandatory for all rentals.

## 9. Rent-out / Equipment Checkout

### 9.1 Required workflow

The PDF requires staff to rent equipment out to a hotel guest or walk-in customer.

Recommended operational flow:

1. Staff selects the customer type: hotel guest or walk-in.
2. If hotel guest, staff selects the relevant guest/stay.
3. Staff selects the equipment item(s).
4. System verifies that each selected item is available for the requested rental period.
5. System resolves the applicable daily seasonal price.
6. Staff records the planned return/due date.
7. Staff records the deposit.
8. Staff confirms the equipment issue.
9. System creates the rental record and marks the item as out for the active rental period.

The PDF does not provide a formal advance reservation workflow. A planned return date is necessary to determine late status, but the precise screen sequence and status names are implementation decisions.

### 9.2 Checkout safeguards

Before issuing an item, verify:
- item exists and is active;
- item is not damaged/unavailable;
- item is not already out for an overlapping rental period;
- price can be resolved for the relevant season/date;
- customer type is valid;
- a hotel guest reference is valid when the hotel guest path is selected.

These are recommended validations to make the PDF's business rules enforceable.

## 10. Return Management

### 10.1 Required behaviour

Staff must be able to record the return of a rented item.

The return operation should record:
- which rental/item is being returned;
- actual return date/time;
- return outcome (normal or marked damaged);
- any additional fee if required.

The PDF does not prescribe a physical inspection checklist or photo capture.

### 10.2 Return state

After a normal return:
- the rental is no longer considered currently out;
- the item can become available for a future rental, subject to any further operational restriction;
- the rental history remains available.

If the item is damaged:
- staff must be able to mark it damaged;
- the damage fee must be recorded;
- the item must not be offered for a new rental while it is considered damaged/unavailable.

The last availability safeguard is a recommended consequence of marking the item damaged. The PDF does not define a repair/reinstatement workflow.

## 11. Damage Management and Damage Fee

### 11.1 Explicit requirement

The PDF says staff can return an item and mark it damaged with a fee.

### 11.2 Required behaviour

- Mark the returned item as damaged.
- Record a damage fee.
- Associate that fee with the rental/customer.
- Retain the damage information in the rental history.
- Make the item unavailable for further rental until its status is resolved (recommended).

### 11.3 Folio integration

For a hotel guest, a damage fee should be posted through Module C's Folio/Charge service when the agreed billing policy says it is chargeable to the room.

The charge should retain:
- the relevant outlet;
- tax information;
- source = rental;
- source rental/item reference where supported.

The PDF's general money design says a charge is never edited or deleted; it is voided with a reason, user and timestamp. Damage fees should therefore use the shared append-only charge process, not a separate editable money field that bypasses Module C.

For walk-in customers, the exact damage-fee collection method is not specified in the PDF. Confirm whether it is collected directly at the rental desk, deducted from a deposit, invoiced or handled by another agreed process.

## 12. Current Rentals and Overdue Items

### 12.1 Explicit requirement

The PDF requires:
- a list of everything currently out;
- overdue items to be highlighted.

### 12.2 Current-out list

A staff screen should list all equipment that has been issued and not yet returned.

Useful information to display:

| Display field | Why it matters |
|---|---|
| Item code | Identify the physical unit |
| Equipment type | Identify the item category |
| Size | Confirm the correct item |
| Customer | Identify who has the item |
| Customer type | Hotel guest or walk-in |
| Issue date/time | Show when it went out |
| Expected return | Determine whether overdue |
| Rental status | Current state |

The PDF mandates the currently-out list, but does not specify every column. The above is a recommended minimum useful display.

### 12.3 Overdue highlighting

An item should be highlighted as overdue when it has not been returned by its recorded expected return boundary.

The exact time-of-day rule, grace period and timezone behaviour are not specified. Use tenant-local time and confirm the due-time convention with the client.

### 12.4 Overdue is not the same as returned late

- **Overdue:** the item is still out and past its due time.
- **Returned late:** the item has come back after its due time and an additional day is added to the bill.

The UI and billing service must distinguish these states. Do not stop highlighting an overdue rental until its return is recorded.

## 13. Late Return and Extra-Day Billing

### 13.1 Explicit acceptance requirement

The PDF's “Done when” check is:

> A late return adds the extra day to the bill.

This is a core business rule.

### 13.2 Required billing outcome

When an item is returned after its agreed due boundary:
- calculate the additional billable rental day(s);
- apply the applicable daily price;
- add the extra amount to the rental's financial outcome;
- send the charge to Module C when it belongs on a hotel guest Folio.

### 13.3 Calculation design

A general calculation model is:

- planned rental amount = sum of applicable daily rates for planned billable days;
- late days = additional billable days caused by actual return being later than the agreed due boundary;
- extra late amount = sum of applicable daily rates for those additional billable days;
- final rental amount = planned rental amount + extra late amount + applicable damage fee, if any.

This is a design explanation, not a complete client-approved pricing formula. The PDF does not define:
- whether a partial extra day is rounded up to a full day;
- exact return cut-off time;
- grace period;
- whether late days use the original season price or the season on each extra day;
- how a late return crossing a season boundary is priced.

These must be confirmed before billing logic is finalised.

### 13.4 Avoid duplicate late charges

The late-return calculation must be safe if the return action is retried or staff reopen the return screen. A single late day must not create multiple identical charges.

Idempotency and a record of which rental days have already been charged are recommended implementation safeguards.

## 14. Integration with Other Modules

### 14.1 Module A — Core & SaaS

Reuse:
- tenant context/database routing;
- outlet/building configuration;
- seasons and dates;
- staff accounts and roles;
- tenant currency, tax and timezone settings.

Do not create another season or staff system.

### 14.2 Module B — Hotel, Chalets & Huts

Reuse:
- guest identity and active Stay;
- hotel guest selection;
- accommodation/stay context.

A walk-in rental must remain possible without a Stay.

### 14.3 Module C — Guest Folio & Checkout

Reuse:
- Folio;
- Charge;
- Payment;
- charge source and outlet attribution;
- append-only money rule;
- void operation.

Expected integrations:
- hotel guest rental fee can be charged to the guest's Folio according to the agreed flow;
- late-return extra day can become a Folio charge;
- damage fee can become a Folio charge;
- deposit collection/return must follow a separately agreed accounting design.

The PDF does not specify the complete payment process for rental transactions. Do not invent a gateway or a deposit-refund process.

## 15. Suggested Data Model

The following is a proposed model decomposition, not a set of table names required by the client. Compare it with the existing `backend/modules/rental/` implementation before creating migrations.

| Proposed model | Responsibility |
|---|---|
| `EquipmentType` | Configurable equipment categories |
| `EquipmentItem` | One physical rentable unit with item code and size |
| `EquipmentSeasonPrice` | Daily price by season, if price is configured by type/item |
| `Rental` | Rental transaction/customer and date summary |
| `RentalItem` | Individual equipment item issued in a rental, if one rental can contain several items |
| `RentalReturn` | Actual return record and return details, if a separate return entity is useful |
| `RentalFee` or shared Folio Charge | Late/damage fee references; monetary source of truth should remain Module C for Folio charges |
| `RentalDepositRecord` | Only if the agreed deposit workflow needs a dedicated accounting record |

### Relationship overview

```text
Tenant database
 ├── EquipmentType
 │    └── EquipmentItem
 │
 ├── Season (Module A)
 │    └── EquipmentSeasonPrice
 │
 ├── Rental
 │    ├── Customer type: hotel guest / walk-in
 │    ├── Stay reference (optional; Module B)
 │    └── RentalItem
 │         ├── EquipmentItem
 │         ├── issue date
 │         ├── expected return
 │         └── actual return
 │
 └── Folio / Charge / Payment (Module C)
      ├── rental charge
      ├── late-return charge
      └── damage fee
```

### Important data rules

- An individual equipment item must have one identifiable item code.
- Rental history must remain after an item is returned.
- Current availability must be derived consistently from item condition and active rental periods.
- Monetary records posted to Folio must use the shared Module C implementation.
- Do not add `tenant_id` to tenant database tables simply because it is a SaaS platform; the project audit says the architecture uses DB-per-tenant. Follow the existing tenant database/routing approach.

## 16. Suggested API Capability Map

The PDF does not define endpoint paths. These are proposed DRF capabilities, not claims that the endpoints already exist.

| Area | Suggested API capability |
|---|---|
| Equipment types | List, create, retrieve, update, deactivate |
| Equipment items | List, create, retrieve, update, mark damaged/available |
| Seasonal prices | List, create, update, resolve daily rate |
| Availability | Search available items by type, size and rental date range |
| Rent out | Issue one or more items to a hotel guest or walk-in customer |
| Current rentals | List all currently-out items |
| Overdue | List/highlight overdue items |
| Return | Record item return and return condition |
| Damage | Mark damaged and calculate/record applicable fee |
| Late billing | Calculate extra billable day(s) and post once |
| Folio integration | Post rental/late/damage charge through Module C |
| Deposit | Record agreed deposit activity after its accounting flow is approved |

### API and security rules

- Use the established project authentication class.
- The project audit identifies a critical authentication configuration issue across B–I ViewSets: `authentication_classes = []` is set while `IsAuthenticated` is required. Fix and test this before relying on Rental APIs.
- Enforce Rental Desk and other approved roles on the backend.
- Check staff outlet access on every operation.
- Do not trust customer, stay, staff actor or outlet references supplied by the client without server-side validation.
- Use service-layer operations for issue, return, damage and late charge calculations.
- Use a database-safe conflict prevention strategy so concurrent requests cannot issue the same physical item twice.
- Use integer minor units for money in accordance with the PDF's technical rules.
- Do not edit/delete posted financial records; follow Module C's append-only/void process.

## 17. Frontend Screens

Suggested screens based on the required workflow:

1. Equipment Types list and form.
2. Individual Equipment Inventory list and form.
3. Equipment Item detail/history.
4. Seasonal Rental Pricing.
5. New Rental / Rent-out screen.
6. Hotel Guest lookup and selection.
7. Walk-in customer entry.
8. Current Rentals / Currently Out list.
9. Overdue Rentals list with clear highlighting.
10. Return Equipment screen.
11. Mark Damaged and record Fee screen.
12. Rental charge/deposit summary.
13. Rental history/search.

The PDF requires the currently-out list and overdue highlighting. The other screens are proposed ways to implement the required operations. The PDF does not prescribe specific screen design or UI library.

## 18. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 reports:

| Item | Audit finding |
|---|---|
| Rental model | Present |
| Rental API | ModelViewSet / CRUD exists |
| Inventory/SKU pool | Missing |
| Conflict service | Missing |
| Rental `services/` | Empty |
| Double-rental prevention | Not implemented |
| Late-return billing | Not implemented/verified |
| Damage fee integration | Not implemented/verified |
| Domain API authentication | Critical defect applies to Rental and other B–I ViewSets |

The audit specifically describes the current implementation as a single Rental row without an inventory/SKU pool or conflict service.

**Do not replace existing Rental code without inspection.** Review current models, migrations, serializers, views, URLs, permissions and tests. Extend what is valid and add migrations without altering already-applied migration files.

## 19. Recommended Development Sequence

Work one step at a time. Each step should include implementation, validation and tests before moving to the next step.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect current Rental code and fix/verify domain API authentication | Authenticated Rental Desk access works; unauthorized requests are rejected |
| 1 | Equipment Type management | Complex can configure the required equipment types |
| 2 | Individual Equipment Item inventory | Every physical item has type, size and unique item code |
| 3 | Seasonal daily pricing | Correct daily price resolves for a season/date |
| 4 | Customer selection | Supports hotel guest and walk-in without forcing a Stay |
| 5 | Rent-out workflow | Issue item and create rental history |
| 6 | Double-rental prevention | Same physical item cannot have overlapping active rentals, including concurrent attempts |
| 7 | Current-out list | All unreturned items are visible |
| 8 | Overdue highlighting | Unreturned items past due boundary are clearly highlighted |
| 9 | Return workflow | Actual return is recorded and item leaves current-out list |
| 10 | Late-return calculation | Late return adds the applicable extra day(s) once |
| 11 | Damage workflow | Item can be marked damaged and damage fee recorded |
| 12 | Folio integration | Guest rental, late and damage charges use Module C correctly |
| 13 | Deposit workflow | Implement only after client confirms deposit accounting and refund/deduction rules |
| 14 | Automated acceptance tests | No double-rental and late return extra-day scenarios pass |

## 20. Test Scenarios

### 20.1 Equipment type and item tests

- Complex can create more than one equipment type.
- Complex can create multiple physical items for one type.
- Each physical item has a size and item code.
- Duplicate item codes are rejected according to the agreed uniqueness scope.
- Inactive/damaged items cannot be issued for a new rental.

### 20.2 Seasonal pricing tests

- An item/type resolves the correct daily price for the active season.
- A missing seasonal price blocks or clearly flags the rental rather than silently using an arbitrary price.
- Historical rental prices do not change after the catalogue price is updated.
- Cross-season rental behaviour follows the client-approved rule.

### 20.3 Customer workflow tests

- Rental can be created for a hotel guest.
- Rental can be created for a walk-in customer without a hotel Stay.
- A hotel guest rental links to the correct guest/stay.
- Staff cannot select a guest/stay from another tenant.

### 20.4 Double-rental tests

- Item A can be rented for one date range.
- A second overlapping rental for Item A is rejected.
- A second rental for Item B can proceed if Item B is available.
- Non-overlapping rentals of the same item can proceed after the prior rental is returned/closed.
- Concurrent requests cannot both successfully issue the same item.

The concurrent test is important: an application-level “check then save” without database-safe locking/constraints can still allow two simultaneous requests to rent the same item.

### 20.5 Return and overdue tests

- An issued, unreturned item appears in the current-out list.
- An item past its expected return boundary is highlighted as overdue.
- Recording a normal return removes it from the current-out list.
- A damaged return is marked damaged and is not offered as available.
- Return history remains accessible.

### 20.6 Late-return tests

- Return on time does not add a late day.
- Return after the agreed due boundary adds the correct extra day.
- Retrying the return operation does not add the same late charge twice.
- A late charge is attributed to the rental/outlet/source as required by the shared billing design.

### 20.7 Damage and Folio tests

- Damaged item can be marked with a fee.
- Hotel guest damage fee is posted to the correct Folio using Module C when the approved billing path applies.
- The charge uses the rental source and correct outlet/tax information.
- Walk-in damage-fee collection follows the client-approved process.
- Voids follow the shared reason/actor/timestamp rules.

## 21. Client Questions to Resolve

The PDF does not answer these details. Obtain confirmation before finalising the relevant business rules:

1. Is the daily price configured per equipment type, per physical item, or by type with an optional item override?
2. Can one rental transaction contain multiple equipment items?
3. Does the item code need to be unique across the whole tenant?
4. Are sizes free text (e.g. “M”, “170 cm”) or should each equipment type have structured size options?
5. Can a customer reserve equipment in advance, or is the MVP strictly rent-out at the desk?
6. What exact time is the expected return due, and is there a grace period?
7. Does any late return past the due time add a full extra day, or is there a cut-off?
8. How are daily prices resolved when a rental crosses from one season into another?
9. Is the deposit mandatory, and is it configured by equipment type or entered by staff?
10. Is a deposit collected, refunded, or deducted from damage/late fees? How must it appear in financial records?
11. What minimum information is required for a walk-in customer?
12. Should rental, late and damage fees always be charged to the hotel guest Folio when a Stay exists, or can the customer pay directly?
13. How are walk-in rental fees and damage fees collected?
14. What happens to an item after it is marked damaged: unavailable indefinitely, or returned to service after an authorised inspection?
15. Who can waive or void a late/damage fee, and what approval/reason is required?

## 22. Definition of Done

Module 5 should not be marked complete until:

- Complex can configure equipment types.
- Individual items are tracked with size and item code.
- Daily rental pricing is resolved by season.
- A deposit can be recorded in the client-approved way.
- Equipment can be rented to a hotel guest or a walk-in customer.
- Staff can record equipment issue and return.
- Staff can mark an item damaged and record a fee.
- Staff can see all currently-out items.
- Overdue items are highlighted.
- The same physical item cannot be rented twice at the same time.
- A late return adds the extra day to the bill.
- Applicable guest charges integrate with Module C without bypassing append-only money rules.
- Automated tests cover item conflict, concurrent issue, return, late day and damage fee.
- The client's two explicit “Done when” scenarios pass.

## 23. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module E, page 4. This is the source for configurable equipment types, size/item code, seasonal price per day, deposit, hotel guest/walk-in rental, return, damage fee, current-out list, overdue highlighting, no double-rental and late extra-day acceptance.
- **Client MVP PDF:** Core concepts and money-flow description, pages 2–3; technical rules, page 7. These define Rental as one rented item from issue to return, Folio/Charge source attribution, append-only money and integer minor units.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, Module E maturity finding and recommended next actions. It reports a Rental model and ModelViewSet, but no individual inventory/SKU pool, no conflict service and an empty Rental services directory; it also identifies the shared B–I authentication defect.

---

**Implementation principle:** Start by reviewing the current Rental app and fixing the shared authentication defect. Then implement individual item inventory before rental issue/return logic. The central rule is that the same physical item must never be issued to overlapping rentals. Add late-day billing only after the client's due-time and rounding rules are confirmed. Do not assume deposit refunds, stock purchasing, QR/barcode scanning or advance reservations are part of the MVP.
