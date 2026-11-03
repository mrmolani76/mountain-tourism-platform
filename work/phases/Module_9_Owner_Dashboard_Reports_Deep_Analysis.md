# Module 9 — Owner Dashboard & Reports
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 5, Module I, page 6; supporting concepts on pages 2–3 and technical rules on page 7  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Convert the client's Module I requirements into an implementation-ready specification, distinguishing explicit PDF requirements from audit findings and recommended implementation decisions.

---

## 1. Module objective

Module 9 provides the Owner with one operational view of the mountain complex and date-range reports across its outlets and services.

The PDF defines two deliverables:

1. **Owner dashboard for Today** — occupancy by building, arrivals and departures with their transport, revenue by outlet, rentals out, open folios, lift status and weather warnings.
2. **Reports for a date range** — revenue by outlet, by service and by payment method, plus weather cancellations, with CSV export.

The most important acceptance rule is that report totals must match the folio charges plus direct sales for the same dates, **to the cent**.

This module is primarily an aggregation and presentation layer. It should read the authoritative records owned by the other modules rather than create duplicate operational or financial records.

## 2. Source requirements and boundaries

### 2.1 Explicit requirements from the client's PDF

| Deliverable | Explicit requirement |
|---|---|
| Owner dashboard | Dashboard for the owner |
| Occupancy | Occupancy by building |
| Arrivals | Today's arrivals, including their transport |
| Departures | Today's departures, including their transport |
| Revenue | Revenue by outlet |
| Rentals | Equipment currently out |
| Folios | Open guest folios |
| Lifts | Current lift status |
| Weather | Current weather warnings |
| Reports | Reports for a selected date range |
| Revenue reporting | Revenue by outlet |
| Service reporting | Revenue by service |
| Payment reporting | Revenue by payment method |
| Weather cancellations | Report weather cancellations |
| Export | CSV export |
| Acceptance | Report totals match folio charges plus direct sales for the same dates, to the cent |

### 2.2 Supporting financial concepts from the PDF

The Core Concepts and money-flow section of the PDF establish these important rules:

- A folio belongs to a stay.
- A charge is one line on a folio and records its outlet, tax and source.
- Every outlet bills a guest folio, while reports still split by outlet.
- Outlet revenue is read from charge lines.
- Cash reporting is read from payment records.
- A bill may be split between cash and card.
- Charges and payments are append-only; they are voided with reason, user and timestamp rather than edited/deleted.

These rules are essential to avoid inaccurate or double-counted reports.

### 2.3 Technical rules from the PDF

The technical section requires:
- money to be stored in integer minor units (for example, cents), never floating-point values;
- timezone, currency and units to be configured per tenant;
- scheduled jobs, reports and the meaning of “today” to use the tenant's local time;
- permissions to be checked by the API, not only hidden in the UI.

### 2.4 Scope boundaries

The PDF does not explicitly require:
- accounting ledger or general-ledger integration;
- tax filing or statutory accounting reports;
- profit/loss, balance sheet or cash-flow statement;
- budgeting, forecasting or targets;
- custom report builder;
- scheduled email reports;
- PDF report export;
- dashboards for every staff role;
- cross-tenant platform analytics for the Super Admin;
- arbitrary user-defined KPIs.

Do not silently add these to the MVP. The specified export is CSV.

## 3. Dashboard — Today

The dashboard is an operational summary for the Owner. “Today” must be evaluated using the complex's configured tenant timezone, not the server's UTC calendar date.

### 3.1 Occupancy by Building

**PDF requirement:** Show occupancy by building.

The dashboard should show accommodation occupancy for each building, using the accommodation inventory from Module B.

Likely accommodation inventory includes:
- hotel rooms;
- chalets;
- hut beds sold individually.

The PDF does not prescribe the exact visualisation, calculation formula or whether unavailable/out-of-service inventory is included in the denominator. A useful proposed breakdown is:

| Building | Total bookable units | Occupied now | Available | Occupancy |
|---|---:|---:|---:|---:|
| Main Hotel | 40 | 28 | 12 | 70% |
| Chalets | 10 | 6 | 4 | 60% |
| Mountain Hut | 20 beds | 14 | 6 | 70% |

Values above are examples only.

**Calculation proposal:**

`occupancy_percent = occupied_bookable_units / total_bookable_units * 100`

Before implementation, define:
- whether a room is occupied based on an active Stay for the current tenant-local date;
- how multi-bed dormitories are counted;
- whether blocked/out-of-service rooms/beds are excluded;
- how partially occupied group accommodation is represented.

Do not use reservation count as a substitute for occupied inventory: one reservation may contain several rooms/beds and a reservation may not have checked in.

### 3.2 Today's Arrivals with Transport

**PDF requirement:** Show arrivals and the transport by which guests arrive.

Data comes primarily from Module B reservations:
- arrival date/time;
- arrival mode (car, train, plane, bus, etc.);
- station/location where applicable.

The PDF's transport model also uses arrival information to create pickup planning tasks for the Transport Desk.

Recommended display fields:

| Field | Purpose |
|---|---|
| Guest / reservation reference | Identify the arrival |
| Expected arrival time | Sort the arrivals |
| Arrival mode | Car, train, plane, bus |
| Station / arrival point | Know where the guest arrives |
| Pickup status | Not required / needs planning / planned |
| Assigned trip | Link to Module G trip if available |

The dashboard is a summary, not a replacement for the detailed reservation or transport planning screens.

### 3.3 Today's Departures with Transport

**PDF requirement:** Show departures with their transport.

Recommended display fields:
- guest/reservation reference;
- planned departure time;
- departure mode;
- station/drop-off point;
- pickup/drop-off trip planning status;
- assigned trip where available.

The PDF does not specify a guest flight/train ticket integration. The platform records the guest's departure information and creates an internal drop-off planning task; it does not purchase external tickets.

### 3.4 Revenue by Outlet — Today

**PDF requirement:** Show revenue by outlet.

Examples of outlets include valley restaurant, mid-station restaurant, summit restaurant, hut kitchen, bar, gear shop, rental and other configured operational outlets.

The dashboard should group sales/revenue by the outlet recorded on the underlying charge/sale. It should not assume that every outlet has a separate till or separate folio.

The platform money-flow rule says outlet revenue is read from charge lines. Direct POS sales must also be included in the overall report reconciliation described later.

Recommended display:
- outlet name;
- revenue amount for tenant-local today;
- optional number of charge/sale records.

The count is a recommendation, not an explicit PDF requirement.

### 3.5 Rentals Currently Out

**PDF requirement:** Show rentals out.

Read from Module E Rental:
- individual items that have been issued;
- have not yet been returned;
- including overdue items if applicable.

A dashboard summary may show total current rentals and a link to the detailed current-out list. The detailed rental screen owns equipment codes, sizes, customer details and overdue processing.

### 3.6 Open Folios

**PDF requirement:** Show open folios.

Read from Module C:
- folios associated with active/unclosed stays;
- current balance/status as available from the Folio service.

The exact definition of “open” should follow Module C's canonical folio status, not a separate dashboard-only status. The PDF does not prescribe whether the dashboard should show every open folio or only open folios with an outstanding balance. A useful design can show both total open folios and the number with a non-zero balance, if agreed.

### 3.7 Lift Status

**PDF requirement:** Show lift status.

Read the current lift status from Module G:
- open;
- closed;
- on hold.

The dashboard must display the latest status and should not independently control lifts. The authoritative update and status history belong to Module G.

### 3.8 Weather Warnings

**PDF requirement:** Show weather warnings.

Read active warnings from Module H, including affected operations and relevant time windows.

The dashboard should surface relevant warning information and provide navigation to the related lift, trip or outdoor service slot. It should not automatically cancel operations.

The weather module owns warning evaluation and staff-approved cancellation.

## 4. Reports — Date Range

### 4.1 Date filter

The PDF requires reports for a date range.

The report interface should let the Owner select:
- start date;
- end date.

**Recommended validation:**
- start date must not be after end date;
- both dates are interpreted in tenant-local time;
- date boundaries must be consistent across all report sections;
- the selected date range must be visible on screen and included in CSV output.

The PDF does not specify preset filters such as Today, This Week or This Month. These may be added as UI conveniences but are not required.

### 4.2 Revenue by Outlet

**PDF requirement:** Report revenue by outlet.

For each outlet, show the sum of its attributable revenue for the selected date range.

The core financial model says every charge line records its outlet. This is the primary attribution source for charges to a guest folio.

Direct sales which are not posted to a guest folio must also be included in the report reconciliation. Their outlet attribution should come from the source POS sale/order.

Recommended report columns:

| Column | Purpose |
|---|---|
| Outlet | Outlet identity |
| Folio charge total | Sum of valid, non-voided charge lines attributed to outlet |
| Direct sales total | Paid-now sales attributed to outlet |
| Total outlet revenue | Folio charges plus direct sales, after applicable void handling |

The PDF does not prescribe this exact column breakdown. It is a proposed way to make the reconciliation transparent.

### 4.3 Revenue by Service

**PDF requirement:** Report revenue by service.

This is distinct from revenue by outlet:
- outlet answers where the revenue was earned;
- service answers what service/product activity generated it.

Examples may include accommodation, restaurant orders, equipment rental, spa treatment, ski lesson, guided hike, transport or lift pass, depending on how source records are classified.

The PDF does not define a formal service taxonomy or exact grouping hierarchy. Use the source charge/order's service/product/source classification. Do not infer a service category only from the outlet name.

Recommended report columns:
- service/category;
- folio charge total;
- direct sales total, where applicable;
- total revenue.

### 4.4 Revenue by Payment Method

**PDF requirement:** Report revenue by payment method.

The platform's money-flow explanation says cash reporting is read from Payment records. Module C supports cash/card recorded by staff and allows multiple payments for one folio.

A report may group payments by:
- cash;
- card;
- any additional payment methods explicitly supported by the project.

The supplied MVP scope explicitly names cash and card. Do not add UPI, bank transfer or gateway methods unless the client approves them.

**Important accounting distinction:**
- Revenue by outlet/service is based on charges and direct sales, as applicable.
- Payments by method are based on recorded payment records.
- A payment is settlement of a folio balance; it is not a second sale.
- Do not add folio payments to folio charges when calculating total revenue, or the same guest spend may be counted twice.
- A split cash/card payment must appear in the respective payment-method totals without changing the outlet attribution of the original charges.

The PDF does not fully specify whether the report should be cash-received within the selected payment date range or payments allocated to sales within the selected sale date range. This date basis must be clearly defined and confirmed.

### 4.5 Weather Cancellations

**PDF requirement:** Include weather cancellations in date-range reports.

Read the cancellation records created by Module H and the affected operational modules (lifts, trips and outdoor service slots).

Recommended fields:
- cancellation date/time;
- affected operation type (lift, trip, service slot);
- operation reference/name;
- weather cancellation reason;
- number of affected bookings;
- associated voided amount, if available.

The PDF explicitly requires reporting weather cancellations, but does not prescribe these columns or whether the report is a count, list, amount summary or all three. A useful MVP report can provide a row-level cancellation list and summary count/void amount.

Weather cancellation is an operational event and should not be added as positive revenue. Its associated voided charges must reduce/neutralise the relevant financial totals according to Module C's rules.

## 5. Critical Financial Reconciliation

This is the most important Module 9 acceptance criterion.

### 5.1 Client's exact acceptance rule

The PDF says:

**Report totals must match the folio charges plus direct sales for the same dates, to the cent.**

This means the report cannot be a manually maintained total or an approximate dashboard number. It must be derived from the underlying source records.

### 5.2 Financial source concepts

The PDF's Core Concepts section says:
- a Charge is a line on a guest Folio;
- a Charge records its outlet, tax and source;
- outlet revenue is read from Charge lines;
- cash reporting is read from Payment records;
- charges/payments are append-only and are voided with reason, user and timestamp.

Therefore, the report should distinguish:

1. **Folio charges:** charges posted to guest folios, with outlet, tax and source.
2. **Direct sales:** sales paid at an outlet/POS rather than charged to a guest folio.
3. **Payments:** amounts recorded to settle folios or direct transactions by payment method.
4. **Voids:** reversals that neutralise a charge/payment according to the shared financial rules.

### 5.3 Avoid double counting

A guest orders dinner and charges it to their room:
- the sale is represented by a charge on the guest folio;
- when the guest later pays the folio by cash/card, that payment settles the balance;
- the payment must not be added again to outlet revenue.

A walk-in customer pays for a meal directly:
- the sale must be included as a direct sale;
- it must not also be posted as a duplicate folio charge.

A guest splits a checkout payment between card and cash:
- payment-method reporting shows the two payment records;
- revenue by outlet/service continues to reflect the original charge attribution.

### 5.4 Reconciliation formula

A recommended high-level reconciliation is:

`report_revenue = valid_folio_charges + valid_direct_sales`

where valid records follow the platform's append-only/void rules and the agreed date basis.

The result must be compared using integer minor units, not floating-point arithmetic.

**Important:** The PDF does not define every date-basis edge case (charge date vs stay date vs payment date), direct-sale schema or tax-inclusion convention. Those decisions must be documented before declaring financial reports complete.

### 5.5 Cent-level accuracy

The PDF's technical rules require money in integer minor units. Therefore:
- sum integer minor-unit amounts;
- avoid float/JavaScript floating-point accumulation for authoritative financial totals;
- use the tenant's configured currency and minor-unit precision;
- return formatted display amounts only after aggregation;
- CSV should contain exact numeric values at the agreed precision.

The project audit notes an existing inconsistency: Folio models use `DecimalField`, while the client technical rules specify integer minor units. Resolve this shared money-model issue with Module C before treating cent-level reporting as production-correct.

## 6. Date and Time Semantics

### 6.1 Tenant-local “Today”

The PDF says that “today” and reports use the tenant's local timezone.

The dashboard's Today query must:
1. read the tenant timezone;
2. determine tenant-local date boundaries;
3. convert the boundaries to the database's stored time basis safely;
4. apply the same interval consistently to all dashboard widgets.

Do not use the server machine's local date as the business date.

### 6.2 Date-range reports

Use a consistent date field for each report type:
- revenue by outlet/service: normally charge/sale effective date, once confirmed;
- payment method: payment recorded date or allocated sale date, once confirmed;
- weather cancellations: cancellation event date/time.

The PDF does not define these date fields in detail. The implementation must label its date basis clearly and apply it consistently.

### 6.3 Daylight saving and timezone boundaries

Tenants may have different timezones. Use timezone-aware date boundary calculations. Do not assume every tenant is in the server timezone or has a fixed UTC offset.

## 7. Data Sources and Module Integrations

Module 9 should aggregate authoritative records; it should not copy all operations into duplicate dashboard tables unless caching/materialised aggregates are needed for performance.

| Dashboard/report item | Authoritative source module |
|---|---|
| Occupancy by building | Module B — Rooms, chalets, hut beds, Stays |
| Arrivals | Module B — Reservations and arrival details |
| Departures | Module B — Reservations and departure details |
| Transport mode / pickup status | Module B + Module G |
| Revenue by outlet | Module C charges + Module D direct POS sales and other outlet charges |
| Revenue by service | Module C charge source + Module D/E/F/G source records |
| Payment method | Module C Payment records and direct POS payment records |
| Rentals out | Module E — Active rentals/items not returned |
| Open folios | Module C — Folio status and balance |
| Lift status | Module G — Current lift status |
| Weather warnings | Module H — Active warning records |
| Weather cancellations | Module H cancellation records + affected Module F/G operations |
| Today's activities | Module F — Service slots/activities |

Do not create a second version of these business records inside the Reports module. Use read services/querysets or a documented reporting projection.

## 8. Suggested Reporting Architecture

The PDF does not prescribe architecture or report model structure. The following is a recommended approach.

### 8.1 Read-only aggregation layer

Create reporting query/services that:
- read from the source modules;
- apply tenant scoping through existing DB-per-tenant routing;
- apply tenant-local date boundaries;
- aggregate integer minor-unit values;
- return dashboard/report DTOs or serialised response data.

Avoid putting complex multi-module aggregation directly inside a Django ViewSet.

### 8.2 Live queries vs cached aggregates

MVP can use database aggregation queries where volume is small and acceptable. If performance needs it, introduce cached/materialised daily aggregates later.

Recommendations:
- begin with accurate source-backed queries;
- measure query performance;
- add indexes for frequently filtered dates, outlet, service/source and status;
- add cached aggregates only with a defined refresh/rebuild strategy;
- never let a stale cache silently become the financial source of truth.

The PDF does not require a data warehouse or a separate analytics database.

### 8.3 Report metadata model

The project audit says a Report model exists but currently provides CRUD on report metadata only, not real aggregates.

A metadata model may still be useful to store saved report configurations, but it is not a substitute for report calculations. The MVP report endpoints must return actual computed figures from Modules B–H and financial source records.

### 8.4 Suggested service organisation

Possible service/query components (names are proposals):

```text
backend/modules/reports/
├── models/
│   └── report.py                 # Existing metadata, if needed
├── serializers/
├── views/
├── services/
│   ├── dashboard_service.py
│   ├── occupancy_service.py
│   ├── arrivals_departures_service.py
│   ├── revenue_service.py
│   ├── payment_method_service.py
│   ├── rental_status_service.py
│   ├── folio_status_service.py
│   ├── operations_status_service.py
│   ├── weather_cancellation_service.py
│   └── csv_export_service.py
├── selectors/                    # Optional read/query layer
├── urls.py
└── tests/
```

The folder split is a proposal. Follow the repository's existing structure rather than creating folders solely to match this example.

## 9. Suggested Dashboard and Report API Capabilities

The PDF does not specify URLs. This is a proposed DRF API capability map, not a claim that these endpoints already exist.

| API capability | Purpose |
|---|---|
| Owner dashboard today | Return all Today dashboard widgets |
| Occupancy by building | Return total, occupied, available and occupancy per building |
| Today's arrivals | Return arrivals with transport details |
| Today's departures | Return departures with transport details |
| Revenue by outlet today | Return outlet-grouped revenue |
| Rentals out | Return current active rentals summary/list |
| Open folios | Return count and optionally non-zero balance summary |
| Lift status | Return current lift statuses |
| Weather warnings | Return active warning summary |
| Date-range revenue by outlet | Return outlet revenue for selected dates |
| Date-range revenue by service | Return service revenue for selected dates |
| Date-range payments by method | Return payment totals by method |
| Date-range weather cancellations | Return cancellation summary/details |
| CSV export | Export the same filtered report data to CSV |

### API rules

- Require authenticated Owner/appropriately authorised role access.
- Enforce tenant scoping server-side through existing tenant database routing.
- Do not rely on frontend route hiding for authorisation.
- Validate date ranges and timezone interpretation.
- Use the same report query/service for on-screen results and CSV export so values cannot diverge.
- Use integer minor units for aggregation.
- Include currency and report date range in response metadata.
- Avoid accepting arbitrary SQL/filter expressions from clients.
- CSV export must use the same permission checks as the corresponding report.
- Protect against CSV formula injection by safely escaping untrusted text fields that begin with spreadsheet formula characters.

## 10. CSV Export

### 10.1 Explicit requirement

The PDF explicitly requires CSV export for date-range reports.

It does not specify XLSX, PDF or emailed reports.

### 10.2 Recommended CSV behaviour

- Export the currently selected report type.
- Apply the exact same date range and filters as the on-screen report.
- Include the tenant currency.
- Include date range and report basis in export metadata or file header where practical.
- Use stable column names.
- Ensure numeric amounts preserve exact minor-unit/currency precision.
- Escape commas, quotes and line breaks correctly.
- Prevent spreadsheet formula injection in user-controlled text fields.
- Enforce the same authorisation as the report endpoint.

### 10.3 Recommended report CSVs

One CSV per report type is a simple MVP approach:
- revenue by outlet;
- revenue by service;
- revenue by payment method;
- weather cancellations.

A combined workbook is not required; the PDF specifies CSV.

## 11. Frontend Screens

Suggested screens:

1. Owner Dashboard / Today.
2. Occupancy by Building widget.
3. Arrivals list with transport.
4. Departures list with transport.
5. Revenue by Outlet widget.
6. Rentals Out widget.
7. Open Folios widget.
8. Lift Status widget.
9. Weather Warnings widget.
10. Reports landing page.
11. Date-range filter.
12. Revenue by Outlet report.
13. Revenue by Service report.
14. Revenue by Payment Method report.
15. Weather Cancellations report.
16. CSV export action.

These are recommended UI components/screens. The PDF defines the data and reports, not a particular dashboard layout or chart library.

## 12. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 reports:

| Item | Audit finding |
|---|---|
| Reports app/model | Present |
| Report model/API | CRUD exists |
| Report metadata | Can be created/managed |
| Real dashboard aggregates | Missing |
| Revenue by outlet/service/payment method | Not implemented as actual aggregates |
| Weather cancellation report | Not implemented as actual aggregate/report |
| CSV export | Not reported as complete |
| Frontend reports | Placeholder / not pilot-ready |
| Domain API authentication | Critical defect affects Reports and other B–I ViewSets |

The audit specifically characterises Module I as **Shell**: Report metadata CRUD exists, but there are no real aggregates.

The audit also says the frontend mountain module pages are placeholders and that the project currently has EQMS-style template pages remaining.

**Do not treat the existing Report CRUD as a complete reporting module.** Inspect the existing Reports models, serializers, views, services and tests. Implement actual read-only aggregation logic and CSV exports.

## 13. Recommended Development Sequence

Implement and verify one step at a time.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Inspect current Reports app; fix/verify shared domain authentication | Authenticated Owner can access Reports APIs; unauthorised role is rejected |
| 1 | Define report metric/date-basis contract | Written definitions for occupancy, revenue, payment date and cancellation date |
| 2 | Resolve money precision inconsistency with Module C | Financial amounts use agreed integer-minor-unit representation |
| 3 | Build read/query service structure | Reports read authoritative source modules without duplicate business records |
| 4 | Occupancy by building | Rooms, chalets and individual hut beds counted correctly |
| 5 | Arrivals and departures | Tenant-local Today lists include transport details |
| 6 | Revenue by outlet | Folio charges plus direct sales grouped by outlet |
| 7 | Revenue by service | Source-backed service attribution and totals |
| 8 | Payment method report | Cash/card totals from payment records without double-counting revenue |
| 9 | Rentals out and open folios widgets | Correct current status from Modules E and C |
| 10 | Lift status and weather warnings | Current data from Modules G and H |
| 11 | Weather cancellation report | Cancellation records and associated void information |
| 12 | Date-range filtering | Consistent tenant-local date semantics |
| 13 | CSV export | Export exactly matches displayed report result |
| 14 | Frontend Owner dashboard | All Today widgets displayed |
| 15 | Frontend Reports screens | Four date-range report views and CSV actions |
| 16 | Reconciliation and regression tests | Totals match folio charges plus direct sales to the cent |

## 14. Test Scenarios

### 14.1 Dashboard Today tests

- “Today” uses the tenant timezone, not the server timezone.
- Occupancy is grouped by the correct building.
- Rooms/chalets and individual hut beds are counted according to the agreed denominator.
- Today's arrivals include arrival mode/time/station.
- Today's departures include departure mode/time/station.
- Pickup/drop-off status is shown when available.
- Rentals out excludes returned items.
- Open folio count follows the authoritative Folio status.
- Lift status reflects the latest Module G status.
- Weather warnings reflect active Module H warnings.

### 14.2 Revenue by outlet tests

- Charges are grouped by their recorded outlet.
- Direct sales are grouped by source outlet.
- Voided charges are neutralised according to Module C rules.
- A room charge is attributed to the outlet that generated it.
- Paying a folio later does not count the same revenue again.
- Total across outlets matches valid folio charges plus direct sales for the selected dates.

### 14.3 Revenue by service tests

- Charges are grouped by their source/service classification.
- Direct POS sales retain product/service attribution.
- No service category is guessed from outlet name.
- Service totals reconcile to the underlying source records.
- Voids are handled consistently.

### 14.4 Payment method tests

- Cash payments are grouped as cash.
- Card payments are grouped as card.
- Split cash/card settlement appears in both method totals.
- Payment totals are not added to outlet/service revenue a second time.
- Payment date range uses the documented date basis.

### 14.5 Weather cancellation tests

- Weather cancellation event appears in the selected range.
- Non-weather cancellations are not classified as weather cancellations.
- Cancellation lists identify the affected operation.
- Associated void amount is shown only once and uses Module C's financial source.
- A void does not become positive revenue.

### 14.6 CSV export tests

- CSV values match the on-screen report.
- CSV respects selected start/end dates.
- CSV uses tenant currency and exact monetary precision.
- CSV includes the expected column headers.
- Commas, quotes and newlines in text fields are escaped correctly.
- Untrusted text cannot execute as a spreadsheet formula.
- Unauthorised staff cannot export Owner reports.

### 14.7 Client acceptance reconciliation test

For one selected date range:
1. calculate valid folio charge lines in the range;
2. calculate valid direct sales in the range;
3. apply voids according to the append-only financial rules;
4. aggregate by outlet/service as applicable;
5. compare report totals with source records.

**Expected:** exact equality to the cent (integer minor unit). No float tolerance or approximate equality is acceptable.

## 15. Client Questions to Resolve

The PDF defines the report names and top-level acceptance but leaves some calculation details open:

1. How should occupancy denominator treat rooms/beds that are inactive, blocked or under maintenance?
2. Should occupancy mean physically occupied now (active Stay), or include arrivals that have not checked in yet?
3. For a room with multiple beds, how should partial occupancy be represented?
4. Should arrivals/departures include only confirmed reservations or all reservation statuses?
5. Which date field determines revenue by outlet/service: charge creation date, service date, stay date or transaction date?
6. For payments by method, should date range use payment recorded date or the original sale/charge date?
7. Are direct POS sales stored as completed POS orders, payment records or another canonical transaction record?
8. How should discounts, tax, rounding and voids be represented in report revenue?
9. Should revenue by service show high-level modules (hotel, restaurant, rental, spa, transport) or individual service/product categories?
10. Should weather cancellation report show only cancellation counts, or also voided amounts and affected guest counts?
11. Should open folios include zero-balance folios awaiting checkout, or only folios with outstanding balance?
12. Which roles besides Owner, if any, may access individual reports?
13. Should CSV exports include row-level transactions or only grouped summaries?
14. What date range maximum is expected for the MVP?
15. Does the client need a separate direct cash report, or is payment-method revenue sufficient?

## 16. Definition of Done

Module 9 should not be marked complete until:

- Owner Dashboard shows occupancy by building.
- Dashboard shows today's arrivals and departures with their transport.
- Dashboard shows revenue by outlet.
- Dashboard shows rentals out.
- Dashboard shows open folios.
- Dashboard shows lift status.
- Dashboard shows weather warnings.
- Reports can be filtered by date range.
- Revenue by outlet is implemented.
- Revenue by service is implemented.
- Revenue by payment method is implemented.
- Weather cancellations are reported.
- CSV export works for the date-range reports.
- Reports read authoritative source records and do not duplicate operational/financial records.
- Date boundaries use tenant-local timezone.
- Financial totals use exact integer minor units.
- Report totals reconcile to folio charges plus direct sales for the same dates, to the cent.
- Automated tests cover date boundaries, voids, split payments, direct sales and reconciliation.

## 17. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 5, Module I, page 6. This is the source for the Owner Today dashboard widgets, date-range revenue reports, payment-method report, weather cancellations, CSV export and cent-level “Done when” criterion.
- **Client MVP PDF:** Core concepts and money-flow diagram, pages 2–3. These define Folio/Charge/Payment, outlet attribution, outlet revenue from charge lines, cash reporting from payments, and append-only void handling.
- **Client MVP PDF:** Technical rules, page 7. These require integer minor units, tenant timezone/currency and server-side permission checks.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, Module I maturity finding and recommended next actions. It reports Report metadata CRUD only, no real aggregates, frontend placeholders and the shared B–I authentication defect.

---

**Implementation principle:** First inspect the existing Reports app and fix/verify authentication. Then define date and money semantics with the source modules, build read-only aggregations from authoritative data, implement the Today dashboard, add the four date-range reports and CSV export, and finish with cent-level reconciliation tests. Do not treat Report metadata CRUD as completed reporting, and never double-count folio payments as additional revenue.
