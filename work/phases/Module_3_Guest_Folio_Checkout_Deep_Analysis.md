# Module 3 — Guest Folio & Checkout
## Deep Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** *Mountain Tourism Complex Platform — MVP Scope v1*, October 2026  
**Relevant client PDF sections:** Core concepts (page 2), money/folio diagram (page 3), Module C — Guest folio and checkout (page 4), technical rules (page 7), Owner dashboard and reports (page 6)  
**Repository audit used:** *Project Audit Report — Mountain Tourism Platform*, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Prepared:** 3 November 2026  

> **Purpose:** Turn the client's Module C scope into an implementation-ready specification. PDF requirements, current audit findings, and additional implementation recommendations are explicitly separated below.

---

## 1. Module objective

The Guest Folio is the single bill for one guest stay. Every charge from the complex's different outlets is posted to that stay's folio, while each charge retains the outlet that earned it. Payments settle the folio and are recorded separately from sales charges.

The module must provide:

- One guest folio for a stay.
- A clear list of all charges with outlet, date, amount and tax.
- A way for any outlet to charge a guest's room using the room number or guest name.
- Cash and card payment recording. This is record-keeping only; no payment gateway is in the MVP.
- Multiple payments against one folio, including split cash/card payments.
- Charge voiding with a reason, restricted to the Manager role.
- A checkout rule that blocks checkout until the balance is zero.
- A printable invoice using browser print or PDF.

The module is not simply an invoice screen or a CRUD interface. It is the shared accounting layer that connects hotel stays, outlet sales, payments, voids and reporting.

## 2. Source-derived domain concepts

The client's PDF defines the following terms in its Core concepts section (page 2):

| Term | Client definition / implication |
|---|---|
| Stay | One visit in a room, chalet or hut bed. |
| Folio | The bill of a stay. There is one bill per stay. |
| Charge | One line on the folio. It always records its outlet, tax and source, such as order, booking, rental or room night. |
| Payment | Cash or card, recorded by staff. One folio can have several payments. |
| Outlet | A business unit in a building, such as a restaurant, shop, rental, spa, sports, transport or lifts. It has its own products, staff and reports. |

### 2.1 One folio per stay

The PDF explicitly says **one bill per stay**. Module B (Hotel, Chalets & Huts) is expected to open the stay and its folio at check-in.

Implications:

- A reservation before arrival is not, by itself, the active stay folio.
- Check-in must create or open the Stay and its corresponding Folio.
- Charges from multiple outlets during that visit go to the same folio.
- Checkout operates against that folio's balance.

The exact database relationship (for example, one-to-one Stay-to-Folio) is a recommended implementation of the one-bill-per-stay rule. The PDF does not prescribe ORM field names.

### 2.2 Charges and payments are different records

A Charge records what the guest owes and which outlet earned the sale. A Payment records how much money staff received and by which method.

Do not model a payment as a charge or overwrite a charge with payment information. This separation is necessary for the PDF's reporting rule:

- Outlet sales/revenue are derived from charge lines.
- Cash/payment reporting is derived from payment records.

### 2.3 Visual workflow in the client PDF

The diagram on page 3 shows Hotel, Restaurants and huts, Shop and rental, Spa and activities, and Transport and lifts all sending charges to one Guest Folio. The folio's charge lines feed outlet reports; cash/card payment records settle the folio and feed payment reporting. The diagram also states that a half-cash/half-card settlement is two payment records.

This means the folio is shared across outlets, but outlet sales must remain attributable to the originating outlet.

---

## 3. Money rules — non-negotiable

### 3.1 Append-only money

The PDF states a hard rule on page 3:

- A charge or payment is never edited or deleted.
- It is voided with a reason, a user and a timestamp.
- A weather cancellation is a void with the reason `weather`.

The technical rules on page 7 further state that money must be stored in **integer minor units (cents), never floats**.

These requirements apply to the folio's charge and payment data, not only to the visible UI.

### 3.2 Recommended void storage approach

The PDF describes the business behaviour, not the database technique. A safe implementation is to preserve the original charge/payment record and append a separate void/reversal event or linked void record containing:

- Original charge/payment reference.
- Void reason.
- User who performed the void, taken from the authenticated server-side user context.
- Timestamp.
- Any required reversal amount/reference.

This preserves the original financial history. Do not physically delete the original record. Do not allow users to edit its amount, outlet, date, source or payment method after posting.

**Important distinction:** Module C's specific bullet says “Void a charge with a reason, manager role only.” The broader page 3 hard rule also says a *payment* is never edited/deleted and is voided with reason, user and timestamp. The PDF does not specify the precise payment-void screen, permissions or approval workflow. Those details need a product decision; the append-only rule still applies.

### 3.3 Server-controlled void actor

The voiding user must come from the authenticated request context, not from a client-supplied `voided_by` field. A browser must not be able to claim that another user performed a void.

This is also consistent with the PDF's requirement to record the user and its technical rule that permissions are checked on the API.

### 3.4 Integer minor units

Store monetary amounts as integer minor units (for example, cents for a currency with two decimal places). Do not use binary floating-point values for money.

The client also requires currency to be configurable per tenant in Module A. Therefore, avoid assuming every tenant uses EUR or a two-decimal currency without checking the tenant's currency configuration and supported precision.

**Current audit conflict:** The audit reports that the existing Folio models use `DecimalField`, while the client's technical rule requires integer minor units. Resolve this at the model/service/API boundary and plan a safe data migration if existing records must be converted. Do not silently change financial values or rewrite migration history.

---

## 4. Charge management

### 4.1 Required charge information

Module C says every folio charge must show:

- Outlet.
- Date.
- Amount.
- Tax.

The Core concepts section adds that every Charge line always records:

- Its outlet.
- Its tax.
- Its source (examples: order, booking, rental, room night).

The source should identify the originating business record/document so an authorised user can trace the charge back to its origin.

### 4.2 Typical charge sources shown or implied by the PDF

| Source area | Example charge | Owning module |
|---|---|---|
| Hotel | Room night and board | Module B — Hotel, Chalets & Huts |
| Restaurant / hut kitchen | Lunch or other meal outside the board plan | Module D — Restaurants, Huts & Shop |
| Shop | Product sale / gear sold | Module D |
| Rental | Equipment rental or damage fee | Module E — Equipment Rental |
| Spa / activities | Sauna, spa or sports service | Module F — Hotel Services & Activities |
| Transport / lifts | Transfer or lift pass | Module G — Transport & Lifts |

The PDF's Core concepts examples include order, booking, rental and room night as charge sources. The list above maps these examples to the module structure in the same PDF.

### 4.3 Charge posting from any outlet

The PDF explicitly states: **Any outlet can charge to a room by room number or guest name.**

Expected business flow:

1. Staff is working in an outlet and selects “charge to room” (or equivalent).
2. Staff searches by room number or guest name.
3. System resolves the guest's current eligible stay/folio.
4. Staff confirms the charge details.
5. System posts the charge to the stay's folio, preserving the originating outlet, date, amount, tax and source.
6. The outlet's sales report can later attribute the charge to that outlet.

**Recommended validation:** If the search returns more than one possible guest/stay, require staff to select the correct active stay rather than silently attaching the charge to the first match. The PDF requires lookup by room number or guest name but does not define the disambiguation UI.

### 4.4 Direct charges and room charges

Module D describes POS payments as “pay now (cash or card) or charge to room.” Module C describes room charges flowing to the folio. This implies the system has to distinguish:

- A sale paid directly at the outlet.
- A sale posted to a guest's room/folio for later settlement.

The exact reporting treatment for direct sales must remain consistent with the broader report rule in Module I. Module C's main responsibility is the guest-folio path and preserving source attribution.

### 4.5 Tax treatment

The folio must display tax for every charge, and Module A provides tenant tax-rate settings. The PDF does not define whether the displayed amount is tax-inclusive or tax-exclusive, whether multiple tax components exist, or the exact rounding method.

**Implementation recommendation:** Decide and document a single tax calculation contract with Module A before coding. Persist enough information on each posted charge to preserve the tax applied at transaction time. Do not recalculate historical charge tax using a tax rate that may have changed later.

---

## 5. Payment management

### 5.1 Supported payment methods

The PDF specifies:

- Cash.
- Card.
- Payments are recorded only; there is **no payment gateway** in the MVP.
- A folio can have several payments.

Do not add online gateway, payment intent, gateway callback or automatic card processing as part of this MVP module unless the client changes the scope.

### 5.2 Multiple payments per folio

One folio may be settled through more than one payment record.

Example from the PDF diagram:

| Payment record | Method | Amount |
|---|---|---:|
| 1 | Card | Half of the amount due |
| 2 | Cash | Remaining half |

The diagram explicitly describes half-and-half payment as two records. Do not collapse this into a single payment row with a mixed payment method.

### 5.3 Payment information

**Explicitly required:** payment method (cash or card), amount/record, and association with the folio.

**Recommended fields:**

| Field | Purpose | Status |
|---|---|---|
| `folio` | Folio being settled | Required by workflow |
| `method` | Cash or card | Explicit in PDF |
| `amount_minor` | Integer minor-unit amount | Derived from technical money rule |
| `received_at` | When staff recorded/received it | Recommended; transaction timestamp is important for audit/reporting |
| `recorded_by` | Authenticated staff member | Consistent with staff-recorded payments and audit requirements |
| `reference` | Optional staff reference | Not specified in PDF |

Do not present the recommended fields as client-mandated fields unless confirmed.

### 5.4 Payment validation

Recommended validations:

- Amount must be a positive supported monetary amount.
- Method must be one of the supported MVP methods: cash or card.
- Payment must belong to the correct folio/tenant.
- Do not allow edits or deletes after posting; apply the append-only void rule.
- Prevent accidental duplicate submission where a request is retried.

The PDF does not define overpayment handling, refunds, payment allocation order, or whether an outstanding folio can accept a partial payment. Multiple payments imply partial settlement should be supported, but refund and overpayment policy require client confirmation.

---

## 6. Void management

### 6.1 Charge void

Module C explicitly requires:

- A charge can be voided with a reason.
- Only the Manager role can perform this action.

The page 3 hard rule adds that a void records the user and timestamp. The original charge must remain traceable.

### 6.2 Weather cancellation

The client gives a global rule: a weather cancellation is a void with reason `weather`.

Module H (Weather) says a staff member explicitly chooses “Cancel for weather” on a trip, a service slot or a lift day. The system cancels affected bookings and voids their charges with reason `weather`. It never makes the cancellation decision automatically.

For Module C, this means:

- Folio must understand voided charges in balance calculation.
- Weather cancellation workflow must be able to invoke the approved charge-void business operation.
- The resulting void must remain attributable to the user/action and timestamp.

The PDF does not fully define whether the weather-driven void uses the same Manager-only UI permission as a manual charge void or a privileged internal service permission. Keep the business rule server-side and have the client confirm the role/approval policy. Do not bypass authorization by exposing an unrestricted void endpoint.

### 6.3 Void validation and audit data

Recommended server-side checks:

1. Authenticated user is present.
2. User has permission for the requested void action.
3. Original charge exists and belongs to the tenant/folio.
4. Original charge is not already voided.
5. A reason is supplied (for weather action, use the required reason `weather`).
6. Actor is taken from `request.user`, never trusted from request payload.
7. A timestamp is generated server-side.
8. Original monetary record is preserved.

The audit report found that the current request body supplies `voided_by`; this should be replaced by the authenticated server-side actor.

---

## 7. Folio balance and checkout gate

### 7.1 Required behaviour

The PDF says: **Checkout is blocked until the balance is zero.**

Checkout must not simply close the folio or mark the guest as checked out when a positive balance remains.

### 7.2 Balance concept

At a business level, the folio balance is the outstanding eligible charges after accounting for valid payments and valid voids/reversals.

A useful conceptual calculation is:

`Outstanding balance = effective charge total - effective payment total`

Where “effective” excludes amounts that have been validly reversed/voided. The exact ledger implementation must follow the append-only rule; never delete the original charge or payment to make a balance reach zero.

### 7.3 Checkout workflow

1. Front desk opens the guest stay and folio.
2. System displays all charge lines, their outlets, dates, amounts and taxes.
3. Staff records the guest's cash/card payment(s).
4. System recalculates the outstanding balance.
5. If balance is not zero, checkout is blocked and the remaining amount is displayed.
6. If balance is zero, checkout may proceed and the stay can be closed through the Module B/C workflow.
7. The invoice can be printed using browser print or PDF.

The role table on page 2 assigns front desk the jobs of reservations, check-in/check-out, folios and payments. Module C does not define all edge cases around reopening a closed folio or post-checkout adjustments; those require policy decisions.

---

## 8. Invoice / printable folio

The PDF requires a printable invoice using either:

- Browser print, or
- PDF.

The folio screen must show every charge with outlet, date, amount and tax. A useful printable invoice should preserve this charge-level detail and reflect the recorded payment/settlement state.

**Recommended invoice content:**

- Complex/business identity and applicable tenant details.
- Guest/stay identifier and stay dates.
- Folio reference.
- Charge date, description/source, outlet, amount and tax.
- Total charges, valid void/reversal impact, payments by method, and outstanding balance.
- Print/PDF layout suitable for handover to the guest.

The PDF does not prescribe invoice numbering, legal tax invoice fields, logo placement, address format, page size or exact wording. These should be confirmed against the tenant's locale and tax/legal requirements before final production.

---

## 9. Reporting rules and outlet attribution

The page 3 diagram makes the accounting separation explicit:

- Outlet sales/revenue comes from charge lines.
- Cash/payment reporting comes from payment records.
- Charges keep their originating outlet even though multiple outlets share one guest folio.

The Module C acceptance criterion further requires that each outlet's report shows only its own sales.

The owner reporting section (Module I, page 6) also expects date-range reports for revenue by outlet, by service and by payment method. Therefore, Module C data must be report-ready and preserve:

- Outlet attribution on each charge.
- Charge date and tax.
- Payment method and payment amount.
- Void/reversal history.

Do not calculate outlet sales by dividing the final folio total among outlets. Use the originating charge lines. Do not derive payment-method totals from charge rows; use payment records.

---

## 10. End-to-end acceptance scenario from the PDF

The exact Module C “Done when” scenario on page 4 is:

1. A summit lunch is charged to the guest's room.
2. A ski lesson is charged to the guest's room.
3. The lesson is cancelled for wind.
4. The guest pays the remaining bill half by card and half by cash.
5. The folio closes at zero.
6. Each outlet's report shows only its own sales.

### 10.1 Expected record-level result

| Record | Expected result |
|---|---|
| Summit lunch charge | Posted to the guest folio with the restaurant/summit outlet attribution, date, amount, tax and order source |
| Ski lesson charge | Posted to the same folio with the services outlet attribution, date, amount, tax and booking/source reference |
| Weather cancellation | Lesson charge is voided/reversed with reason `weather`, with user and timestamp; original charge remains traceable |
| Card payment | First payment record, for half of the remaining amount |
| Cash payment | Second payment record, for the other half of the remaining amount |
| Folio balance | Zero after the valid void/reversal and both payments are accounted for |
| Outlet reporting | Summit lunch belongs to its originating outlet; voided ski lesson does not remain as earned sales; cash/card payments feed payment reports separately |

The last row expresses the intended accounting outcome from the PDF's diagram and test scenario. Exact report presentation of voided transactions (for example, separate gross sales and void totals) is not specified, so retain the void history and agree the display with the client.

### 10.2 Additional acceptance tests recommended

These cases supplement, but do not replace, the client's exact scenario:

- One folio receives charges from at least two distinct outlets.
- One folio receives multiple card/cash payments.
- Partial payment leaves a non-zero balance and checkout remains blocked.
- Exact-zero balance permits checkout.
- A charge void requires Manager permission and a reason.
- A non-manager cannot void a charge through a direct API call.
- Original charge/payment records remain available after void.
- Void actor cannot be spoofed through request payload.
- A repeated void request cannot reverse the same charge twice.
- Tenant A cannot view, charge, pay, void or checkout Tenant B's folios.
- Outlet report totals are based on charges; payment-method report totals are based on payments.
- Tax displayed on an old charge remains the tax applied at posting time even if tenant tax settings later change.

---

## 11. Relationship with other modules

| Module | Integration with Folio |
|---|---|
| A — Core & Super Admin | Tenant context, staff identity, roles, outlet access, currency/tax/timezone settings, and audit logging for every charge, payment and void |
| B — Hotel, Chalets & Huts | Check-in opens the Stay and Folio; nightly room and board charges post to it; checkout depends on zero balance |
| D — Restaurants, Huts & Shop | POS can charge to a guest room; included board meals must not be sold/charged again; extra items can be charged to folio |
| E — Equipment Rental | Rental and damage charges can be associated with guest folio where appropriate |
| F — Hotel Services & Activities | Spa/activity/lesson bookings can generate folio charges; cancellation may require a void |
| G — Transport & Lifts | Transfers and lift-pass purchases can generate folio charges |
| H — Weather | A staff-authorised weather cancellation voids affected charges with reason `weather` |
| I — Owner Dashboard & Reports | Revenue by outlet/service from charges; payment method totals from payment records; void/cancellation reporting |

The exact charge integration method (synchronous service call, event, or another mechanism) is an architecture decision. Whichever mechanism is chosen, all modules must use one shared folio business service rather than implementing separate, inconsistent billing logic.

---

## 12. Suggested data model

The following is a proposed decomposition, not a list of table names mandated by the PDF. Reconcile it with the existing repository before creating or modifying migrations.

| Proposed model | Responsibility | Source alignment |
|---|---|---|
| `Folio` | One bill for one Stay; current balance/closure state may be derived or maintained safely | One bill per stay |
| `Charge` | Immutable monetary line with folio, outlet, amount, tax and source reference | Core concepts + Module C |
| `Payment` | Immutable cash/card receipt against a folio | Core concepts + Module C |
| `ChargeVoid` / `VoidEvent` | Append-only reason, actor and timestamp linked to original charge | Global append-only money rule |
| `PaymentVoid` / `VoidEvent` | Append-only void data for a payment, if payment void functionality is exposed | Global append-only money rule; detailed workflow unspecified |
| `FolioInvoice` (optional) | Snapshot/reference for generated printable invoice, if required | Optional implementation design; not mandated |

### 12.1 Suggested Charge attributes

- Folio reference.
- Outlet reference.
- Charge date/time.
- Description.
- Amount in integer minor units.
- Tax amount and/or stored tax breakdown in integer minor units.
- Currency context.
- Source type and source record/document reference (for example, order, booking, rental, room night).
- Created/posted by staff user.
- Created timestamp.

The PDF explicitly requires outlet, date, amount, tax and source. Description, source-type normalisation and user-created metadata are recommended for traceability.

### 12.2 Suggested Payment attributes

- Folio reference.
- Method: cash or card.
- Amount in integer minor units.
- Received/recorded timestamp.
- Recorded-by staff user.
- Currency context.

Do not store gateway transaction IDs as required MVP data because the PDF says no gateway is involved. An optional staff reference may be useful but is not specified.

### 12.3 Tenant architecture

The project audit describes a DB-per-tenant architecture. Folio, Charge, Payment and related records should live in the tenant database and use the established tenant database routing. Do not add a redundant `tenant_id` field to tenant database tables without first checking the current architecture and project rules.

---

## 13. Suggested Django REST Framework structure

The current audit identifies the backend domain package as `backend/modules/folio/`. Use the existing module and inspect current files before adding new ones.

**Conceptual structure (adapt to the repository's existing layout):**

```text
backend/modules/folio/
├── models/
│   ├── folio.py
│   ├── charge.py
│   ├── payment.py
│   └── void_event.py
├── serializers/
│   ├── folio.py
│   ├── charge.py
│   ├── payment.py
│   └── void.py
├── views/
│   ├── folio.py
│   ├── charge.py
│   ├── payment.py
│   └── invoice.py
├── services/
│   ├── folio_service.py
│   ├── charge_service.py
│   ├── payment_service.py
│   ├── void_service.py
│   └── balance_service.py
├── permissions.py
├── urls.py
└── tests/
    ├── test_folio.py
    ├── test_charge.py
    ├── test_payment.py
    ├── test_void.py
    ├── test_checkout.py
    └── test_reporting_attribution.py
```

This is a suggested organisation. Keep the current repository conventions if they differ; do not duplicate existing modules or services merely to match this example.

### 13.1 Suggested API capabilities

The PDF does not prescribe URL paths or endpoint names. These are the API operations the application needs to support:

| Capability | Purpose |
|---|---|
| Get folio | Retrieve folio for a stay, including charges, payments and balance |
| Search active stay | Resolve guest by room number or guest name for charge-to-room |
| Post charge | Add an immutable charge from an authorised outlet/source |
| List charges | Show charge date, outlet, amount, tax and source |
| Record payment | Record cash/card payment against folio |
| List payments | Show multiple payment records and methods |
| Void charge | Manager-authorised charge void with reason, actor and timestamp |
| Void payment | Only if approved by product policy; preserve the global append-only rule |
| Get balance | Return current outstanding balance based on valid ledger entries |
| Print invoice | Return printable HTML/browser view or PDF |
| Checkout validation | Verify zero balance before closing the stay |

### 13.2 Service-layer rules

Business rules should live in services/domain operations, not only in ViewSet methods or frontend components. A service should be reused by POS, Hotel, Rental, Services and Transport modules when they post a folio charge.

For operations that write related records (for example, charge posting, void event creation, payment recording and balance updates), use database transactions and suitable locking/idempotency controls to avoid partial or duplicate financial operations.

These are implementation recommendations, not explicit technology choices in the PDF.

---

## 14. Permissions and role expectations

The role table on page 2 defines:

- **Owner:** works across everything in the complex.
- **Manager:** works in assigned outlets; manages products, prices, voids and outlet reports.
- **Front desk:** works with hotel, chalets and hut; reservations, check-in/check-out, folios and payments.
- **Cashier:** works with restaurants, hut and shop; orders, payments and charge to room.

Module C specifically restricts charge void to the Manager role.

| Operation | PDF-supported role expectation |
|---|---|
| View/manage guest folios | Front desk has folio responsibilities; Owner works across everything |
| Record guest payments | Front desk and Cashier roles have payment responsibilities in their areas |
| Charge to room | Any outlet can charge to a room; Cashier's role explicitly includes charge to room |
| Void a charge | Manager role only, per Module C |
| Checkout | Front desk, per role table |
| Outlet sales report | Manager has assigned outlet reports; Owner has reports across the complex |

The exact per-action permission matrix for every outlet and the policy for weather-driven voids need to be enforced server-side and confirmed where the PDF is silent. Do not rely on frontend button visibility as authorization.

---

## 15. Frontend screens and user experience

The PDF does not provide visual designs. The following screens are a recommended UI decomposition based on its required operations:

1. **Guest folio detail:** guest/stay identity, folio balance, charge list, payment list.
2. **Charge list/table:** date, description/source, outlet, amount, tax and void state/history.
3. **Charge-to-room lookup:** search by room number or guest name; select the correct active stay.
4. **Record payment:** choose cash/card and enter amount; allow multiple records.
5. **Void charge dialog:** Manager-only; require a reason and show the charge being voided.
6. **Balance summary:** total effective charges, payments and outstanding amount.
7. **Checkout validation:** explain why checkout is blocked when balance is not zero.
8. **Printable invoice:** browser print and/or generated PDF.
9. **Audit/history view:** trace charge, payment and void actions where user permissions allow.

Keep the screen usable on the staff laptop/tablet context described by the client. Do not add gateway/online payment UI to the MVP.

---

## 16. Current repository audit — what exists and what is missing

The 3 November 2026 audit reports the following for Module C:

| Area | Audit finding | Interpretation |
|---|---|---|
| Folio models | Present | Domain model foundation exists |
| Charge / Payment models | Present | Money records exist in the codebase |
| Folio services | Open/close, post charge, record payment and void service layer present | Core operations have some implementation |
| Void/create path | Reported as OK in services | Service-level path exists, but not fully acceptance-tested |
| Update/delete protection | ViewSets block update/delete (405) | Direction is consistent with append-only money rule |
| Amount representation | Uses `DecimalField` | Conflicts with PDF's integer-minor-unit technical rule |
| `voided_by` | Taken from request body | Must be set from authenticated server user |
| Model `void()` helper | Reported broken: assigns `models.DateTimeField.auto_now` incorrectly | Model helper needs correction/removal; audit says service uses `timezone.now()` correctly |
| Charge/payment audit trail | Not wired | Module A's audit-log requirement is not fully connected to folio operations |
| Checkout integration | Module C partial; Module B check-in does not create folio | End-to-end stay-to-folio lifecycle is incomplete |
| Tests | No folio/void/checkout money tests reported | Critical business behaviour lacks automated evidence |
| Authentication | Folio ViewSets set `authentication_classes = []` while requiring `IsAuthenticated` | Critical domain API authentication defect |

### 16.1 Critical authentication defect

The audit identifies this pattern in B–I domain ViewSets, including Folio:

- `authentication_classes = []`
- `permission_classes = [IsAuthenticated, ...]`

This clears the default `StaffSessionAuthentication`, so staff sessions do not bind as intended and protected requests fail authorization (the audit describes the observed effect as 403) or become unsafe if permission checks are weakened.

**Required before relying on Folio APIs:** remove the empty authentication override or configure the intended `StaffSessionAuthentication` consistently with Core/SaaS. Add authenticated API tests. Do not weaken `IsAuthenticated` to work around the issue.

### 16.2 Test coverage warning

The audit reports approximately 38 test definitions, but they are under core/SaaS/auth/isolation, with zero Folio/Hotel/POS/money tests. The full test suite was not run as part of that audit. Therefore, existing service code must not be considered proven merely because it is present.

**Important:** This document is based on the audit report, not a fresh source-code execution or test run. Re-inspect the current repository before changing implementation because the working tree may have changed after the audit.

---

## 17. Development sequence — one step at a time

Recommended sequence for implementation and verification:

| Step | Work item | Why / completion evidence |
|---:|---|---|
| 0 | Inspect existing Folio models, services, migrations, views, URLs and tests | Establish exactly what is present; avoid duplicate models or migrations |
| 1 | Fix and test domain API authentication | Authenticated staff can access protected Folio APIs; unauthenticated requests are rejected |
| 2 | Confirm money representation and safe integer-minor-unit migration | Charge and Payment amounts follow the PDF's money rule without changing historical values incorrectly |
| 3 | Formalise immutable Charge and Payment operations | Create/record operations work; update/delete remain blocked |
| 4 | Fix void business operation | Manager-only charge void; reason, authenticated actor and timestamp retained; original charge preserved |
| 5 | Wire charge/payment/void audit logging | Module A audit requirement is recorded for every relevant event |
| 6 | Implement/verify balance calculation | Charges, voids and payments produce correct outstanding balance |
| 7 | Integrate Module B check-in and Stay | Check-in opens the associated folio |
| 8 | Integrate outlet charge-to-room lookup | Room number/guest name resolves the correct active stay; outlet/source attribution retained |
| 9 | Implement multiple cash/card payment recording | One folio accepts several payment rows; half-and-half is two rows |
| 10 | Implement zero-balance checkout gate | Non-zero balance blocks; zero balance permits checkout |
| 11 | Implement printable invoice | Browser print or PDF shows folio charge and settlement detail |
| 12 | Connect weather-driven void path | Explicit “Cancel for weather” operation voids affected charge with reason `weather` |
| 13 | Verify outlet and payment reporting | Outlet sales derive from charges; payment totals derive from payment rows |
| 14 | Run acceptance and regression tests | Client's Module C “Done when” scenario passes; no cross-tenant or authorization regressions |

### Suggested priority

- **P0:** Authentication defect and financial data integrity.
- **P1:** Charge/payment services, void correctness, actor identity and audit trail.
- **P2:** Balance calculation and Module B stay/folio integration.
- **P3:** Charge-to-room integrations and multiple payments.
- **P4:** Checkout gate and invoice.
- **P5:** Weather void integration, reporting and full acceptance regression.

This prioritisation is a recommendation based on the audit risks and the client's acceptance scenario. It does not replace the project's existing work-item/approval process.

---

## 18. Definition of done

Do not mark Module C complete until all of the following are verified:

- [ ] One folio exists for each active stay.
- [ ] Folio shows every charge with outlet, date, amount and tax.
- [ ] Every charge retains its source/source document.
- [ ] Any outlet can charge the correct guest room using room number or guest name.
- [ ] Charge and payment money records cannot be edited or deleted after posting.
- [ ] Charge void requires Manager permission and a reason.
- [ ] Every void retains the user and timestamp; actor is set server-side.
- [ ] Cash and card payments can be recorded without a gateway.
- [ ] One folio accepts multiple payments; half cash and half card creates two records.
- [ ] Folio balance is correct after charges, payments and voids.
- [ ] Checkout is blocked until balance is zero.
- [ ] Invoice is printable through browser print or PDF.
- [ ] Charge/payment/void activity is connected to the audit log.
- [ ] Outlet reporting attributes sales to the originating outlet.
- [ ] Payment reporting derives from payment records.
- [ ] Weather cancellation can void the related charge with reason `weather` through an authorised path.
- [ ] Automated tests cover the complete Module C acceptance scenario.
- [ ] Tenant isolation and API authorization tests pass.

---

## 19. Client questions / business decisions not answered by the PDF

Do not silently invent these rules. Capture the client's answer before finalising the relevant workflow:

1. **Payment voids:** Are staff allowed to void a payment, who may do it, and is a refund record or separate cash refund process required?
2. **Overpayment:** Can a folio have a negative balance/guest credit, or must payments be limited to the amount due?
3. **Refunds:** Are refunds in MVP, or are only charge/payment voids required?
4. **Tax display:** Are amounts tax-inclusive or tax-exclusive? Can a charge have multiple tax components? What rounding convention applies?
5. **Invoice requirements:** What invoice numbering, company details, tax identifiers, language, currency formatting and legal fields are required for pilot complexes?
6. **Charge ownership:** Can a Manager void a charge originating from another outlet, or only charges for assigned outlets?
7. **Charge-to-room eligibility:** Can a charge be posted only to an in-house checked-in stay, or can it be posted to a future reservation/pre-arrival folio?
8. **Guest search ambiguity:** What additional information should staff see when multiple guests share a name or room history exists?
9. **Checkout and late charges:** Can staff post a final charge after checkout, and if yes, how is the closed folio reopened or adjusted without editing history?
10. **Payment references:** Does the client need optional card slip/reference number or cashier/terminal details even though no gateway is used?
11. **Void report display:** Should reports show gross charges and voids separately, or net outlet sales with a separate void column?
12. **Weather permission:** Which role may execute a weather cancellation that triggers a folio void: Manager only, or an authorised Weather/Operations role through a server-side service?

---

## 20. Source references and evidence boundaries

### Client PDF

- **Page 2 — Core concepts:** Stay, Folio, Charge and Payment definitions; a Charge records outlet, tax and source; one folio can have several payments.
- **Page 3 — Money rule and diagram:** money is append-only; charge/payment is never edited/deleted; void requires reason, user and timestamp; one guest folio receives charges from every outlet; outlet sales come from charges and cash/payment reporting from payments; half-and-half is two payment records.
- **Page 4 — Module C:** folio charge fields, charge-to-room by room number or guest name, cash/card recording only without gateway, multiple payments, Manager-only charge void with reason, zero-balance checkout, printable invoice, and the summit lunch/ski lesson/weather/half-card-half-cash acceptance scenario.
- **Page 3 / Module A:** audit log for every charge, payment and void.
- **Page 6 — Module I:** date-range reporting by outlet/service/payment method and CSV export; totals must match folio charges plus direct sales to the cent.
- **Page 7 — Technical rules:** money stored in integer minor units (cents), never floats; permissions checked on API.

### Project audit — 3 November 2026

- **Section 3 — Module maturity:** Folio models, services and void exist; status Partial; Decimal money, missing audit trail wiring and broken domain authentication are noted.
- **Section 5 — Findings:** `voided_by` comes from request body; model `void()` helper is broken; no domain money/checkout tests; domain ViewSets clear authentication classes.
- **Section 6 — Strengths:** Folio service layer has open/close, post charge, record payment and void operations.
- **Section 7 — Verification:** full test suite was not run in that audit.
- **Section 8 — Recommended next actions:** fix domain authentication; complete check-in-to-folio, nightly posting, integer money and void actor; add folio void/checkout/board-posting tests; implement POS charge-to-folio and board-inclusion guard.

---

## Final implementation principle

**One Stay → One Folio → Many immutable Charges → Many immutable Payments → Zero-balance Checkout.**

Build the shared financial service once and let each outlet use it. Preserve outlet/source attribution on every charge, keep payments separate, and never edit or delete posted money. First inspect and repair the existing Folio implementation; then close the PDF acceptance gaps one by one.
