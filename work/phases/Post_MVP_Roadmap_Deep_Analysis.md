# Post-MVP — Product Roadmap & Deep Technical Analysis
## Future Scope, Dependencies, Architecture Considerations and Delivery Plan

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Section 9, page 8  
**Supporting source:** MVP technical rules, Section 7, page 7; hosting/CI/CD, Section 6, pages 6–7; handover, Section 11, page 9  
**Prepared:** 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Document purpose:** Convert the client's post-MVP wishlist into a structured roadmap for future planning. This document does not change the MVP contract.

---

## 1. Important Scope Clarification

The client's PDF explicitly states that Section 9 is **After the MVP**. These items are not part of the current 12-week MVP contract unless separately agreed in writing.

The current MVP is the 9-module scope A–I. Post-MVP functionality should be planned only after the MVP is running, handed over and validated with the pilot complexes.

This document separates:

- **Client-stated future scope:** Features explicitly listed in Section 9.
- **Recommendation:** Proposed architecture, sequence, dependencies, security controls, acceptance tests and implementation approach. These details are not fully specified by the PDF and need client approval before becoming contractual scope.

## 2. Client's Post-MVP Feature List

The PDF lists the following future directions:

| # | Client-stated future feature | Meaning from the PDF |
|---:|---|---|
| 1 | Own domain, subdomain per complex, proper SSL | Give each complex its own subdomain and configure SSL |
| 2 | Self-signup and monthly subscriptions | Complexes can sign up and pay subscription fees online |
| 3 | Public booking website | Public website for bookings |
| 4 | Guest web portal | Guests can use a phone browser to receive weather alerts, book services, obtain lift passes and view their bill |
| 5 | Lift gate and RFID card integration | Connect platform lift passes to physical lift access hardware |
| 6 | Offline mode for huts | Support huts with weak network connectivity |
| 7 | SMS or WhatsApp weather alerts | Notify guests about weather alerts |
| 8 | Richer weather data | Add snow depth and freezing level using a paid provider if pilots request it |
| 9 | Real train and flight ticket booking | Book train/flight tickets through partners |
| 10 | Channel manager and OTA sync, accounting export, multi-currency | Integrations for accommodation distribution and accounting, plus multiple currencies |

**Source note:** These are future ideas listed by the client, not detailed implementation requirements. The PDF does not specify providers, data schemas, workflows, commercial terms, acceptance criteria or delivery dates for these features.

## 3. Recommended Readiness Gate Before Post-MVP

Before starting these workstreams, confirm that the MVP is stable enough to extend.

### 3.1 Recommended entry conditions

- MVP Modules A–I have completed their agreed acceptance checks.
- Tenant isolation is verified by automated tests.
- Authentication and permissions work across all tenant APIs.
- Core money records follow the agreed append-only and void rules.
- Folio, direct sales and reporting totals reconcile.
- Booking, capacity and equipment-conflict rules are covered by tests.
- Weather fetch, warning propagation and manual cancellation are operational.
- Database backup and restore have been tested.
- Deployment rollback and release process have been tested.
- API documentation and operational handover are complete.

These are recommended readiness gates based on the MVP's own technical and handover requirements. They are not additional Section 9 requirements.

### 3.2 Why the gate matters

Post-MVP features increase operational and integration complexity. Examples:
- subscriptions introduce billing and account lifecycle states;
- guest portal exposes selected data outside staff workflows;
- offline mode introduces synchronisation and conflict resolution;
- RFID integration connects software permissions to physical access;
- OTA sync can create duplicate or conflicting reservations;
- multi-currency affects pricing, charges, payments, reporting and exports.

Building these before the MVP foundations are reliable increases the risk of rework.

## 4. Workstream 1 — Own Domain, Subdomain per Complex and Proper SSL

### 4.1 Client-stated goal

Each complex should have its own subdomain under the platform's domain, with proper SSL.

Example hostnames (illustrative only):
- `complex-a.example.com`
- `complex-b.example.com`

The PDF does not specify the final domain name or hostname format.

### 4.2 Recommended architecture

A tenant-aware request flow may look like:

```text
Browser
  |
  v
complex-a.platform-domain
  |
  v
TLS / reverse proxy
  |
  v
Tenant hostname resolver
  |
  v
Tenant context
  |
  v
Central tenant registry
  |
  v
Tenant database connection
  |
  v
Application APIs
```

The existing MVP audit describes a DB-per-tenant architecture with a central SaaS registry and tenant-specific databases. The domain resolver should map a verified hostname to the correct tenant record before tenant data is accessed.

### 4.3 Recommended implementation tasks

1. Choose and register the platform's production domain.
2. Define the hostname convention for each complex.
3. Add a verified tenant-domain mapping in the central SaaS database.
4. Validate hostnames against registered active tenants.
5. Resolve tenant context before tenant-specific API access.
6. Configure DNS and TLS certificate issuance/renewal.
7. Redirect HTTP to HTTPS.
8. Reject unknown or unverified hostnames safely.
9. Update frontend API routing to use the active tenant host.
10. Test isolation across at least two tenant hostnames.

### 4.4 Security considerations

- Do not trust an arbitrary `Host` header as tenant identity without validation.
- Prevent one tenant from registering another tenant's hostname.
- Use HTTPS for all tenant subdomains.
- Protect session cookies with secure settings.
- Ensure CORS and allowed-host configuration are explicit.
- Do not reveal whether another tenant exists through hostname error messages.
- Keep tenant resolution tests in the automated suite.

### 4.5 Acceptance tests — proposed

- A verified domain resolves to the correct tenant.
- An unknown domain cannot access tenant data.
- Tenant A cannot access Tenant B data by changing hostname or identifiers.
- HTTP redirects to HTTPS.
- Certificate is valid and renewal is operational.
- Tenant login and existing staff workflows work on the new subdomain.
- Domain changes require configuration/registry changes, not hard-coded application URLs.

These are recommended tests; the PDF only states the goal of own domain, a subdomain per complex and proper SSL.

## 5. Workstream 2 — Self-Signup, Monthly Subscriptions and Online Payments

### 5.1 Client-stated goal

The client wants:
- self-signup;
- monthly subscriptions;
- online payments.

This implies a SaaS commercial layer where a complex can register and subscribe without the platform operator manually creating every account.

### 5.2 Recommended SaaS lifecycle

A proposed onboarding lifecycle:

```text
Public signup
    |
    v
Create pending tenant/account
    |
    v
Verify email / required business details
    |
    v
Select subscription plan
    |
    v
Checkout with payment provider
    |
    v
Payment confirmed by verified webhook
    |
    v
Provision or activate tenant
    |
    v
Owner completes complex setup
    |
    v
Subscription active
```

This flow is a recommendation. The PDF does not specify email verification, trial period, plan tiers, billing cycle rules or tenant provisioning timing.

### 5.3 Functional areas to design

**A. Signup**
- complex/business details;
- owner contact details;
- credentials and account verification;
- duplicate signup handling;
- terms and privacy consent as required.

**B. Subscription plans**
- plan name;
- billing amount;
- currency;
- billing interval;
- included limits/features;
- active/inactive state.

The PDF only says monthly subscriptions. It does not define tiered plans, feature limits or usage caps.

**C. Subscription lifecycle**
Potential states may include:
- pending;
- trialing (only if trial is approved);
- active;
- payment overdue/past due;
- suspended;
- cancelled;
- expired.

These states are a proposed model, not client-specified states.

**D. Payment integration**
- provider-hosted checkout or secure payment flow;
- verified server-side webhooks;
- transaction reference and status;
- retry/failed-payment handling;
- invoice/receipt according to local requirements.

The payment provider, gateway fees and supported payment methods must be selected by the client.

**E. Tenant activation**
- define when a tenant database is provisioned;
- define whether a tenant is activated only after payment;
- define whether subscription failure suspends login, restricts features or provides a grace period;
- ensure financial and tenant lifecycle records are auditable.

### 5.4 Security and reliability

- Never trust the browser's payment-success redirect as proof of payment.
- Verify webhook signatures server-side.
- Make webhook processing idempotent.
- Do not store raw card details.
- Separate subscription payments from guest folio payments.
- Record subscription status transitions.
- Define a secure recovery process for failed provisioning.

### 5.5 Acceptance tests — proposed

- A new complex can complete signup.
- Duplicate signup attempts are handled safely.
- Subscription payment is confirmed only by a verified provider event.
- A webhook retry does not create duplicate subscription charges or tenants.
- Active subscription enables the agreed product access.
- Failed/overdue payment follows the approved grace/suspension policy.
- Cancellation and reactivation follow an auditable lifecycle.

## 6. Workstream 3 — Public Booking Website

### 6.1 Client-stated goal

The client wants a public booking website.

The PDF does not detail:
- which inventory is bookable publicly;
- whether the site is per complex or shared platform-wide;
- guest account requirements;
- deposit/payment policy;
- booking modification/cancellation rules;
- language and currency behaviour.

### 6.2 Recommended scope discovery

Before implementation, decide:
- public pages for each complex;
- accommodation types exposed (rooms, chalets, hut beds);
- availability search and pricing display;
- reservation creation;
- guest information and arrival details;
- board-plan selection;
- deposit/payment handling;
- confirmation and cancellation communication;
- whether services, rental and lift passes are also bookable publicly.

### 6.3 Recommended architecture

The public booking site should use the same tenant-aware backend and booking services as the staff application.

Do not create a second reservation engine. The public website and staff front desk should share:
- availability logic;
- pricing rules;
- reservation validation;
- deposit/payment records;
- tenant and season rules.

### 6.4 Acceptance tests — proposed

- Public visitor sees only the intended complex's inventory.
- Availability and price match staff application values.
- Concurrent bookings cannot reserve the same room/bed twice.
- Booking details are visible in the staff reservation system.
- Guest arrival and board plan information is saved consistently.
- Public users cannot view another guest's reservation or folio.

## 7. Workstream 4 — Guest Web Portal

### 7.1 Client-stated goal

A guest portal must work on any phone without installing an app. The PDF lists these capabilities:
- weather alerts;
- book services;
- lift passes;
- see the bill.

### 7.2 Recommended guest identity and access

The PDF does not specify how guests authenticate. Options requiring client decision:
- email/password account;
- secure booking-reference link;
- one-time code;
- account created at reservation/check-in.

Security must prevent one guest from accessing another guest's stay, bookings or bill.

### 7.3 Functional areas

**A. Guest stay overview**
- current stay dates;
- accommodation reference;
- relevant guest information.

**B. Weather**
- relevant complex/point weather information;
- warnings/alerts;
- preference and consent controls if notifications are added.

**C. Service booking**
- browse available services;
- view slot capacity;
- book a service;
- view/cancel booking according to policy;
- see whether service is included or paid.

Use Module F's common booking engine rather than creating portal-only bookings.

**D. Lift passes**
- view available pass products;
- purchase or request pass according to payment policy;
- see pass details/validity if supported.

Use Module G lift-pass products and Module D/C sale/payment services.

**E. Bill / Folio**
- view charges and payments for the guest's own stay;
- show current balance;
- show invoice/receipt if enabled.

The portal must be read-only for financial records unless a separately approved payment workflow is added.

### 7.4 Accessibility and device support

The PDF says the portal must work on any phone and require no app install. A responsive web application/PWA-style experience may be considered, but a native app is not required.

### 7.5 Acceptance tests — proposed

- Guest accesses only their own stay and folio.
- Portal works in mobile browsers.
- Service availability matches front desk.
- Guest-created booking appears in the staff application.
- Included services are not charged twice.
- Lift pass sale is consistent with staff POS/front desk.
- Bill totals match Module C.

## 8. Workstream 5 — Lift Gate and RFID Card Integration

### 8.1 Client-stated goal

The client wants lift gate and RFID card integration after the MVP.

The MVP explicitly says no gate hardware is included. This workstream is a future integration and should not be accidentally added to MVP.

### 8.2 Decisions required first

- Which lift gate/RFID hardware vendor will be used?
- Which RFID card types/readers are supported?
- Does the vendor expose an API, SDK or local controller protocol?
- Is access control online-only or must it work during network outages?
- How is a lift pass encoded/activated?
- Can a pass be suspended, revoked or reissued?
- How are duplicate card assignments handled?
- What happens if a guest loses a card?
- Is access granted by date, time, lift type, zone or pass category?

The PDF does not answer these questions.

### 8.3 Recommended integration architecture

```text
Lift Pass Product / Guest Purchase
             |
             v
Issued Lift Pass
             |
             v
Card Assignment / RFID Identifier
             |
             v
Integration Adapter
             |
             v
Vendor Gate System
             |
             v
Access Events / Sync Status
```

Keep vendor-specific logic inside an integration adapter. Do not spread hardware protocols throughout the core booking or folio code.

### 8.4 Recommended data concepts

- issued pass;
- card/RFID identifier;
- guest/pass association;
- validity window;
- allowed access category;
- card status (active, blocked, lost, replaced);
- sync state with gate provider;
- gate event if the vendor provides it.

These are design proposals, not PDF-specified schema.

### 8.5 Acceptance tests — proposed

- Eligible issued pass can be associated with a card.
- Valid card/pass combination is recognised by the approved gate system.
- Expired/revoked pass is not accepted.
- Lost/replaced card follows approved process.
- Integration failure is visible to staff.
- Gate events reconcile with pass/guest identity where supported.

## 9. Workstream 6 — Offline Mode for Huts

### 9.1 Client-stated goal

The client wants offline mode for huts with weak signal.

The PDF does not define which workflows must work offline, how long a hut may remain disconnected, or how conflicts are resolved.

### 9.2 Offline scope must be defined

Potential offline workflows to discuss:
- view cached guest/stay information;
- view room/bed allocations;
- record meal orders;
- record service/rental activity;
- record payments or payment intent;
- queue changes until connection returns.

Do not assume all modules can safely work offline. Financial and inventory operations can conflict across devices or locations.

### 9.3 Recommended architecture

A cautious approach is a local-first queue for specifically approved workflows:

```text
Staff browser/device
      |
      v
Local encrypted storage / pending-operation queue
      |
      | network unavailable
      v
User continues approved offline actions
      |
      | connection restored
      v
Sync engine
      |
      v
Backend validation and conflict handling
      |
      v
Sync result / staff feedback
```

A service worker/PWA may help cache the application shell, but that alone does not make the business workflows safely offline.

### 9.4 Data and conflict considerations

- Identify which records can be cached and for how long.
- Protect guest personal information on shared devices.
- Encrypt sensitive local data where supported.
- Use unique operation IDs for idempotent sync.
- Preserve the time and device that recorded each queued operation.
- Revalidate permissions and business rules on the server during sync.
- Define conflicts for room/bed reservations, rental item allocation, limited-capacity service slots and seat-based trips.
- Define how a staff member sees rejected/conflicted changes.

Never assume an offline booking is confirmed until the server accepts it where a shared resource could have been booked elsewhere.

### 9.5 Acceptance tests — proposed

- Approved screens remain usable without a network.
- Offline changes are clearly marked pending.
- Reconnection syncs each operation once.
- Conflict is detected and shown to staff.
- No duplicate charge/payment is created by retry.
- Cached guest data follows approved expiry/security rules.

## 10. Workstream 7 — SMS or WhatsApp Weather Alerts

### 10.1 Client-stated goal

Send weather alerts to guests via SMS or WhatsApp.

The PDF does not specify:
- provider;
- message templates;
- which warning levels trigger guest notifications;
- recipient selection;
- language;
- consent/opt-in;
- delivery retry policy;
- message cost ownership.

### 10.2 Recommended flow

1. Module H creates or updates a weather warning.
2. Determine whether the warning qualifies for guest notification under an approved rule.
3. Identify eligible guests whose stays/bookings are affected.
4. Check notification consent and contact availability.
5. Build the approved message template.
6. Send through the selected SMS/WhatsApp provider.
7. Record provider response, delivery state and retry status.
8. Avoid duplicate notification for the same guest/warning/version.

### 10.3 Important separation

Weather warning and guest notification are different:
- a warning is an internal operational event;
- a notification is an external communication to a guest.

Do not send every forecast threshold crossing to every guest automatically. The client must approve recipient rules, notification severity and opt-in/consent behaviour.

### 10.4 Recommended delivery log

- warning reference;
- guest/contact reference;
- channel (SMS or WhatsApp);
- template/version;
- provider message ID;
- queued/sent/delivered/failed state;
- timestamps;
- retry count.

### 10.5 Acceptance tests — proposed

- Only eligible guests receive notifications.
- Consent/opt-in rules are respected.
- Repeated forecast fetches do not spam guests.
- Provider failures are recorded and retry policy is bounded.
- Staff can see notification outcome.
- Message content uses the approved language/template.

## 11. Workstream 8 — Richer Weather Data: Snow Depth and Freezing Level

### 11.1 Client-stated goal

The client may want richer weather data such as:
- snow depth;
- freezing level.

The PDF says this may come from a paid provider **if the pilots ask for it**.

This is conditional future scope, not a confirmed immediate requirement.

### 11.2 Recommended decision gate

Before selecting a provider:
- collect pilot-complex requirements;
- confirm which metrics are operationally useful;
- identify coverage/altitude accuracy needed;
- compare data update frequency and forecast horizon;
- check commercial licensing and caching terms;
- confirm cost, rate limits and support;
- confirm whether historical data is needed.

### 11.3 Provider abstraction

Do not hard-code one paid weather provider throughout the Weather module.

Recommended structure:

```text
Weather Provider Interface
      |
      +--> MET Norway adapter (MVP)
      |
      +--> Paid provider adapter (future, if approved)
```

Normalise provider responses to internal weather fields while retaining source/provider and retrieval metadata.

### 11.4 Acceptance tests — proposed

- Approved provider supplies snow-depth/freezing-level data for supported points.
- Missing provider values do not break the existing MET Norway forecast.
- Provider failover/fallback behaviour follows an agreed policy.
- Licensing, caching and attribution requirements are satisfied.
- Extra provider cost is visible/approved.

## 12. Workstream 9 — Real Train and Flight Ticket Booking Through Partners

### 12.1 Client-stated goal

The client wants real train and flight tickets to be bookable through partners.

The MVP only records arrival/departure details and uses that information to plan pickups/drop-offs. Ticket booking is a future integration.

### 12.2 Decisions required

- Which train and flight booking partners?
- Which countries/routes are in scope?
- Does the platform only redirect to the partner, or create a booking through an API?
- Who is merchant of record?
- Who collects payment?
- Who handles cancellation/refund/changes?
- How are booking references and ticket documents stored?
- How are passenger names and identity details protected?
- What commission or partner fees apply?

The PDF does not identify any partner or commercial arrangement.

### 12.3 Recommended integration models

**Model A — Partner redirect**
- guest searches route/date;
- platform redirects to partner;
- partner completes booking;
- guest returns with booking reference if available.

**Model B — API-assisted booking**
- platform queries partner inventory;
- guest selects itinerary;
- platform submits booking;
- partner confirms and returns ticket/booking details.

Model B is substantially more complex and should only be selected after partner API and commercial access are confirmed.

### 12.4 Transport integration

Where a real ticket booking is linked to a guest reservation, arrival/departure details may populate the existing transport pickup/drop-off planning flow.

The external booking should not bypass the reservation/transport data model.

### 12.5 Acceptance tests — proposed

- Partner availability and booking status are handled correctly.
- Confirmed booking reference is linked to the right guest/reservation.
- Failed/timeout partner response does not create a false confirmed ticket.
- Cancellation/change response is reconciled with the partner.
- Arrival details can be used for pickup planning.

## 13. Workstream 10 — Channel Manager, OTA Sync, Accounting Export and Multi-Currency

This is a grouped future workstream in the PDF. Each element has separate dependencies and should be estimated separately rather than treated as one small feature.

### 13.1 Channel Manager and OTA Sync

**Client-stated goal:** Connect to a channel manager and synchronise with online travel agencies (OTAs).

The PDF does not name a channel manager or OTA partners.

**Recommended scope discovery:**
- selected channel manager;
- selected OTAs;
- supported accommodation types;
- availability sync;
- rate sync;
- reservation import;
- modification/cancellation sync;
- room mapping;
- board-plan mapping;
- sync frequency and error handling;
- overbooking policy;
- source-of-booking attribution.

**Recommended architecture:**
- external channel adapter;
- internal accommodation/availability mapping;
- inbound reservation normalisation;
- outbound availability/rate sync;
- integration log and retry queue;
- conflict/error dashboard.

Do not create one custom code path per OTA if the chosen channel manager provides a shared integration API.

**Critical risks:**
- duplicate reservations;
- mapping errors between room types;
- stale availability;
- conflicting rate/season rules;
- cancellation updates not received;
- time-zone mismatches;
- overbooking due to sync delay.

### 13.2 Accounting Export

**Client-stated goal:** Export accounting data.

The PDF does not specify the accounting software, export format, chart of accounts or tax rules.

Before implementation decide:
- target accounting system(s);
- CSV or API export;
- account/category mapping;
- tax code mapping;
- revenue and payment mapping;
- void/refund representation;
- export date basis;
- reconciliation and duplicate-export handling.

**Recommended architecture:**
- map platform source records to an accounting export DTO;
- generate a validated export batch;
- record export status and external reference;
- prevent duplicate export of the same transaction;
- provide an error report for unmapped accounts/tax codes.

Accounting export should use the platform's authoritative charge/payment/void records and must not alter the original financial records.

### 13.3 Multi-Currency

**Client-stated goal:** Support multiple currencies.

The MVP technical rules currently say currency is configured per tenant. Multi-currency extends this model and affects financial integrity.

Decisions required:
- Is each tenant configured with one reporting/base currency and allowed transaction currencies?
- Can a single folio contain charges in multiple currencies?
- Which exchange-rate source is used?
- At what time is the exchange rate fixed?
- Are rates manually entered or fetched from a provider?
- How are exchange-rate differences represented?
- Is reporting shown in transaction currency, base currency, or both?
- How are refunds/voids handled after the exchange rate changes?

**Recommended principle:**
- store transaction currency with each monetary transaction;
- store the amount in integer minor units for that currency;
- if converting, persist the applied exchange rate, rate timestamp/source and converted amount;
- never recalculate historical converted values using today's exchange rate;
- define rounding rules per currency;
- ensure financial reports clearly identify the currency basis.

Multi-currency must be designed across Modules C, D, E, F, G and I rather than added only as a display preference.

### 13.4 Acceptance tests — proposed

**Channel manager/OTA**
- External booking creates one internal reservation.
- Reservation updates/cancellations sync correctly.
- Duplicate inbound events are idempotent.
- Room/rate mapping is validated.
- Sync failures are visible and retryable.

**Accounting export**
- Export contains expected transactions and tax/account mapping.
- Voided transactions are represented correctly.
- Repeated export does not duplicate entries.
- Export totals reconcile to platform reports.

**Multi-currency**
- Each transaction retains its original currency and exact minor-unit amount.
- Converted values retain rate and timestamp.
- Historical reports do not change when current rates change.
- Rounding is deterministic and tested.

## 14. Recommended Dependency Map

The PDF lists the future features but does not define their order. The following is a recommended dependency map.

| Workstream | Main dependency |
|---|---|
| Own domain/subdomain/SSL | Stable tenant registry and tenant resolution |
| Self-signup/subscriptions | Tenant provisioning, payment/webhook handling, subscription lifecycle |
| Public booking website | Stable reservation, availability, pricing and payment rules |
| Guest portal | Guest identity/access, Stay, Folio, Services, Lift Pass and Weather APIs |
| RFID/lift gate | Lift Pass product/issued-pass model and selected hardware vendor |
| Offline huts | Stable workflows, sync identifiers, conflict policy and data security |
| SMS/WhatsApp alerts | Weather warning model, guest contact/consent and messaging provider |
| Snow depth/freezing level | Pilot demand and selected paid weather provider |
| Train/flight ticket booking | Selected travel partners and commercial/API access |
| Channel manager/OTA | Stable room inventory, rates, reservations and partner mapping |
| Accounting export | Stable charge/payment/void model and target accounting system |
| Multi-currency | Agreed currency/exchange-rate model across all financial modules |

## 15. Recommended Delivery Priority

This is a planning recommendation, not a sequence stated by the client.

### Phase P1 — SaaS commercial foundation
- own domain and tenant subdomains;
- SSL and hostname tenant resolution;
- self-signup;
- monthly subscription plans and payment handling.

**Reason:** Establishes the commercial and tenant-access foundation for scaling the product to multiple complexes.

### Phase P2 — Guest digital experience
- public booking website;
- guest web portal;
- service booking;
- lift-pass purchase/view;
- guest bill and weather information.

**Reason:** Reuses the operational booking, folio and weather functionality delivered in the MVP.

### Phase P3 — Operational connectivity
- SMS/WhatsApp weather notifications;
- pilot-approved richer weather data;
- RFID/lift gate integration.

**Reason:** Depends on stable warning, lift-pass and hardware/provider choices.

### Phase P4 — External ecosystem integrations
- channel manager/OTA sync;
- accounting export;
- train/flight booking partners.

**Reason:** Requires partner contracts, mappings, testing and operational support.

### Phase P5 — Advanced resilience and finance
- offline mode for huts;
- multi-currency.

**Reason:** Both cut across many core workflows and require careful conflict/financial design. Multi-currency may be moved earlier if pilot business requirements demand it, but it should be treated as a cross-platform financial project.

## 16. Recommended Technical Workstream Structure

A possible repository organisation is shown below as a conceptual guide only. Follow the existing project architecture and avoid unnecessary new apps.

```text
backend/
├── modules/
│   ├── saas/                 # tenant registry, signup, subscription
│   ├── core/                 # tenant domains and settings
│   ├── hotel/                # public booking uses shared hotel services
│   ├── folio/                # guest bill and financial source of truth
│   ├── services/             # common service booking engine
│   ├── transport/            # lift passes and operations
│   ├── weather/              # weather alerts and provider adapters
│   ├── integrations/         # future external adapters, if justified
│   └── reports/              # financial/operational export
│
frontend/
├── staff-app/
├── public-booking/
└── guest-portal/
```

This is not a requirement to split the current Nuxt app or create an `integrations` module immediately. The architectural decision should consider the existing repository and deployment constraints.

## 17. Cross-Cutting Requirements and Risks

### 17.1 Tenant isolation

Every post-MVP feature must preserve tenant isolation:
- subdomain resolves to exactly one tenant;
- guest portal token/user is tied to the right tenant and stay;
- background jobs run with explicit tenant context;
- integrations cannot read another tenant's records.

### 17.2 API security

- Enforce role/resource permissions on the server.
- Rate-limit public signup, login and guest-access endpoints.
- Protect webhook endpoints with signature validation and idempotency.
- Use short-lived or revocable guest access tokens where appropriate.
- Keep private lobby links and guest portal links separate in scope and permissions.

### 17.3 Payments and financial integrity

- Do not store raw card data.
- Subscription payment ledger must be distinct from guest Folio payments.
- Use append-only financial records.
- Voids/refunds must preserve reason, actor and timestamp.
- Multi-currency must not use floating-point money values.
- Reconcile external payment events before activating paid services.

### 17.4 Integration reliability

External providers can be unavailable or send duplicate/out-of-order events. Recommended shared practices:
- idempotency keys;
- integration event log;
- bounded retry with backoff;
- dead-letter/manual review state;
- provider request/response correlation IDs;
- alerting for repeated failures;
- clear staff-facing sync status.

### 17.5 Privacy and consent

Guest contact, travel and stay information is sensitive operational data. Before public portals and messaging:
- define minimum personal data needed;
- define who can view/edit it;
- establish notification consent and opt-out rules;
- limit personal data in printed lists;
- define retention and deletion policy;
- protect shared-device cached guest data for offline mode.

### 17.6 Vendor and commercial dependencies

The PDF does not name providers for:
- online payments;
- SMS/WhatsApp;
- RFID gates;
- paid weather;
- train/flight booking;
- channel management/OTAs;
- accounting software.

These workstreams cannot be fully estimated until the client identifies providers and confirms access, contracts, costs and API capabilities.

## 18. Client Questions Before Post-MVP Estimation

1. What is the platform's production domain and preferred tenant subdomain format?
2. Who purchases/controls DNS and SSL certificates?
3. Does self-signup require manual approval before tenant provisioning?
4. Will subscription plans have different feature limits?
5. Is there a free trial or grace period for failed payments?
6. Which payment provider will handle SaaS subscription billing?
7. Is the public booking website branded separately for each complex?
8. Are guest accounts created during reservation, check-in or via invitation?
9. Should guests pay online in the portal, or only view the bill initially?
10. Which lift gate/RFID vendor and hardware model are planned?
11. Which exact workflows must work offline in huts?
12. How long can a hut remain offline, and which device(s) are authoritative?
13. Which weather warning levels should trigger SMS/WhatsApp messages?
14. Which messaging provider, language and consent policy will be used?
15. Which paid weather provider is preferred if pilots request snow-depth/freezing-level data?
16. Which train/flight booking partners are available and contracted?
17. Which channel manager and OTAs are in scope?
18. Which accounting software and export format are required?
19. Which currencies are required, and what is the tenant's reporting/base currency?
20. Which exchange-rate provider and conversion-time policy should be used?

## 19. Post-MVP Acceptance Planning Checklist

Before accepting a workstream as complete, define:
- named business owner;
- approved provider/vendor;
- detailed user stories;
- data ownership and source of truth;
- tenant isolation and permissions;
- API contract;
- failure and retry behaviour;
- audit trail;
- automated acceptance tests;
- operational support procedure;
- costs and recurring vendor fees;
- migration/backward-compatibility plan;
- monitoring and handover documentation.

The PDF does not provide these acceptance details for Section 9, so each post-MVP feature needs a separate approved scope before development.

## 20. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Section 9, page 8. This is the source for all ten future directions: own domain/subdomains/SSL; self-signup/monthly subscriptions/public booking; guest portal; lift gate/RFID; offline huts; SMS/WhatsApp weather alerts; richer weather data; train/flight partner booking; channel manager/OTA sync; accounting export and multi-currency.
- **Client MVP PDF:** Technical rules, Section 7, page 7. These establish the MVP's tenant, money, permission, API and timezone principles that future functionality should preserve.
- **Client MVP PDF:** Hosting/CI/CD, Section 6, pages 6–7, and handover, Section 11, page 9. These provide deployment, rollback, backups, documentation and operational baseline considerations.
- **Important source qualification:** Section 9 says these are not part of the contract and describes them as future intentions if the MVP goes well. The detailed designs, priorities, questions and tests in this document are recommendations, not commitments in the client's PDF.

---

**Roadmap principle:** Finish and validate the 9-module MVP first. Then prioritise post-MVP work based on pilot feedback, partner availability and business value. Do not treat the future wishlist as an approved sprint backlog until each workstream has a confirmed scope, provider, acceptance criteria and budget.
