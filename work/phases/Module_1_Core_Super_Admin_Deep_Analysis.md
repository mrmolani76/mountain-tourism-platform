# Module 1 — Core & Super Admin
## Detailed Functional Analysis and Development Specification

**Project:** Mountain Tourism Complex Platform  
**Source scope:** Mountain Tourism Complex Platform — MVP Scope v1 (October 2026), Sections 2–5, pages 1–3; supporting technical rules, Section 7, page 7  
**Project audit reference:** Project Audit Report — Mountain Tourism Platform, 3 November 2026  
**Implementation context:** Django REST Framework backend and Vue/Nuxt frontend  
**Purpose:** Convert the client's Module A requirements into an implementation-ready specification. Explicit PDF requirements, current repository audit findings, architecture conflicts and implementation recommendations are separated below.

---

## 1. Module objective

Module 1 is the foundation of the Mountain Tourism SaaS platform. It provides the platform-level administration and the core configuration every mountain complex needs before it can operate the other modules.

It must support:

- staff login with email and password;
- platform Super Admin access;
- creation of a mountain complex and its Owner account;
- activation/deactivation of a complex;
- building management, including building type, altitude and map position;
- outlets inside buildings;
- staff accounts with roles and outlet access;
- season configuration with dates;
- complex settings for currency, tax rates, timezone and measurement units;
- audit logging for every charge, payment and void;
- tenant isolation so one complex cannot see another complex's data.

The client's core acceptance rule is that two complexes on the same server cannot see each other's data. This must be proven by an automated test, not only by manually clicking through the UI.

Module A is not just a settings CRUD area. It establishes the identity, tenant boundary, roles, location hierarchy and operational configuration used by Modules B–I.

## 2. Source requirements and boundaries

### 2.1 Explicit requirements from the client's PDF

The PDF's Module A section (Section 5, page 3) explicitly requires:

| Area | Requirement stated in the PDF |
|---|---|
| Authentication | Login with email and password |
| Staff password reset | Owners and Managers reset staff passwords themselves |
| Email | No email sending in the MVP |
| Super Admin | Super Admin page |
| Complex creation | Super Admin can create a complex |
| Owner account | Super Admin creates the complex's Owner account |
| Complex status | Super Admin can activate or deactivate a complex |
| Buildings | Buildings have type, altitude and map position |
| Outlets | Outlets exist inside buildings |
| Staff | Staff accounts have a role and outlet access |
| Seasons | Seasons have dates |
| Settings | Currency, tax rates, timezone and units |
| Units | Temperature and wind units (°C and km/h, or °F and mph) |
| Audit | Audit log for every charge, payment and void |

### 2.2 Related user roles from the PDF

The Users and Roles section on pages 1–2 defines these roles and main responsibilities:

| Role | Works in | Main jobs from the PDF |
|---|---|---|
| Super Admin | Super Admin page | Create complexes and their Owner accounts; activate/deactivate complexes |
| Owner | Everything | Settings, buildings, outlets, staff, prices, weather limits and reports |
| Manager | Assigned outlets | Products, prices, voids and outlet reports |
| Front Desk | Hotel, chalets, hut | Reservations, check-in/check-out, folios and payments |
| Cashier | Restaurants, hut, shop | Orders, payments and charge to room |
| Rental Desk | Rental shop | Rent out, returns and damage fees |
| Service Staff | Spa, sauna, sports, ski school | Slots, bookings, own schedule and guided-outing headcount |
| Transport Desk | Transport | Shuttles, taxi trips, pickups, vehicles and drivers |
| Lift Operator | Lift stations | Lift status from a phone |

The PDF separately states that drivers do not log in for the MVP; they receive a printed passenger list.

### 2.3 Core concepts from the PDF

The PDF defines:

- **Tenant:** one complex.
- **Building:** a place in the complex with type, map position and altitude. Every building is also a weather point.
- **Outlet:** a business unit in a building, such as restaurant, hut kitchen, shop, rental, spa, sports, transport or lifts.
- **Season:** winter, summer or closed, with dates. Prices, menus and services may differ by season.

These are shared concepts. Modules B–I should reuse them instead of creating their own independent building, outlet or season registries.

## 3. Platform and Tenant Model

### 3.1 Tenant meaning

A tenant represents one Mountain Tourism Complex.

The platform may host multiple tenants on the same application/server, but every tenant's operational data must remain isolated from every other tenant.

Example:

```text
Mountain Tourism SaaS Platform
        |
        +-- Complex A
        |     +-- Owner A
        |     +-- Buildings
        |     +-- Outlets
        |     +-- Staff
        |     +-- Seasons and settings
        |
        +-- Complex B
        |     +-- Owner B
        |     +-- Buildings
        |     +-- Outlets
        |     +-- Staff
        |     +-- Seasons and settings
        |
        +-- Complex C
              +-- Owner C
              +-- Buildings
              +-- Outlets
              +-- Staff
              +-- Seasons and settings
```

A user belonging to Complex A must not access Complex B's buildings, staff, reservations, folios, products, rental records or reports.

### 3.2 Important architecture difference to resolve

There is an architecture detail in the supplied sources that must not be silently ignored:

- The PDF's Core Concepts section (page 2) says that every table carries `tenant_id` and that the tenant is known from the login, with no subdomains in the MVP.
- The PDF's technical rules (page 7) require tenant isolation in one place, using Postgres row-level security or a scoped repository through which every query goes.
- The 3 November repository audit describes the current implementation as **DB-per-tenant**, with a central database for SaaS registry records and tenant-specific databases for tenant modules. It says tenant tables do not carry `tenant_id`.
- The audit's architecture snapshot also describes host-based separation between the central SaaS host and tenant host.

These statements are not identical architecture descriptions.

**Required decision:** Confirm whether DB-per-tenant is an accepted implementation alternative to the PDF's shared-table `tenant_id` approach, and confirm how the “no subdomains in the MVP” requirement relates to the current host-based routing. Do not change the architecture or add/remove tenant identifiers until this is resolved with the project owner/client.

**Resolved 2026-11-03 (item 29, MVP freeze):** DB-per-tenant + Host/`Domain` routing is the accepted and implemented MVP isolation story (see `docs/DECISIONS.md` 2026-11-02 Accepted rows + 2026-11-03 Module 1 freeze row). It supersedes the PDF's shared-table `tenant_id` wording for MVP; business rows carry no `tenant_id`; isolation is the active DB connection (`tenancy/` + `DATABASE_ROUTERS`), enforced per request by `TenantResolveMiddleware`. No purchased subdomain is required: host routing works on a shared VPS hostname/sslip.io or IP:port, and the `Domain` registry maps host → tenant; staff login binds the session to the issuing tenant (BUG-TENANT-001). No architecture rewrite is planned for MVP.

### 3.3 Current repository architecture according to the audit

The audit reports the following current direction:

```text
Central SaaS database
    |
    +-- modules.saas
          +-- Tenant
          +-- Domain
          +-- SaasUser

Tenant database
    |
    +-- modules.core
    |     +-- Staff
    |     +-- Building
    |     +-- Outlet
    |     +-- Season
    |     +-- Settings
    |     +-- Authentication
    |
    +-- hotel
    +-- folio
    +-- pos
    +-- rental
    +-- services
    +-- transport
    +-- weather
    +-- reports
```

The audit also reports tenancy middleware, database connections and a database router under `backend/tenancy/`.

This is a snapshot of the audited repository, not a replacement for the client's contractual architecture decision.

## 4. Super Admin Management

### 4.1 Super Admin purpose

Super Admin operates at platform level, not as the Owner of one mountain complex.

The Super Admin's responsibilities explicitly defined in the PDF are:

1. Create a new mountain complex.
2. Create the Owner account for that complex.
3. Activate a complex.
4. Deactivate a complex.

Super Admin must be separate from tenant-level Owner, Manager and operational staff roles.

### 4.2 Complex creation workflow

Recommended workflow derived from the PDF:

1. Super Admin opens the Super Admin page.
2. Selects Create Complex.
3. Enters the complex's required identity details.
4. Creates the Owner account for that complex.
5. System creates/registers the tenant according to the approved tenancy architecture.
6. System associates the Owner account with the new tenant.
7. Complex is assigned an initial status.
8. Super Admin can later activate/deactivate it.

The PDF does not prescribe the exact complex fields, approval process, automatic database-provisioning behaviour or initial status. These must match the existing SaaS provisioning implementation and be confirmed where unclear.

### 4.3 Activation and deactivation

The PDF requires the Super Admin to activate/deactivate a complex.

Expected platform behaviour:
- active tenant can use the platform according to its permissions/subscription or agreed MVP access policy;
- deactivated tenant is prevented from normal platform use;
- deactivation must not delete tenant data;
- reactivation restores access without recreating the tenant.

The last two are safety recommendations. The PDF does not specify whether deactivation is temporary, commercial, security-related or permanent. Do not implement automatic deletion or data destruction.

### 4.4 Recommended audit trail

For platform-level status changes, record:
- tenant/complex reference;
- old status;
- new status;
- actor;
- timestamp;
- reason, if required by policy.

The PDF explicitly requires audit logging for charge/payment/void records, not a complete Super Admin audit schema. The above is recommended security/accountability practice.

## 5. Owner Account and Tenant Staff Management

### 5.1 Owner account

The Super Admin creates the Owner account as part of creating a complex.

The Owner is the tenant-level administrator and, according to the PDF, can access everything within that complex.

Owner responsibilities include:
- settings;
- buildings;
- outlets;
- staff;
- prices;
- weather limits;
- reports.

Owner access must remain scoped to their own tenant.

### 5.2 Staff accounts

The PDF requires staff accounts with:
- a role;
- outlet access.

Expected capabilities:
- create staff account;
- assign a role;
- assign one or more outlet permissions;
- update staff information/access;
- reset staff password according to the PDF's reset rule;
- deactivate staff access when no longer authorised.

The exact profile fields, invitation process and staff lifecycle statuses are not specified.

### 5.3 Role assignment

The available role set must represent the roles defined in the PDF:
- Owner;
- Manager;
- Front Desk;
- Cashier;
- Rental Desk;
- Service Staff;
- Transport Desk;
- Lift Operator.

Super Admin is a platform-level role, not a normal outlet staff role.

Do not give every staff user Owner-level permissions. The API must enforce role and outlet scope on every protected operation.

### 5.4 Outlet access

The PDF says every staff account has outlet access.

This means:
- a staff user may be assigned to the outlet(s) where they work;
- the role defines the type of action they can perform;
- outlet access restricts the scope of records they can operate on.

Example:
- Manager A may be assigned to the Summit Restaurant and Gear Shop.
- Cashier B may be assigned to the Valley Restaurant only.
- Lift Operator C may access assigned lift stations/status operations.

These examples illustrate the model; exact access combinations must follow the client's role policy.

### 5.5 Password reset

The PDF's MVP rule is specific:

**Owners and Managers reset staff passwords themselves; no email sending in the MVP.**

Implications:
- do not make email sending a dependency for MVP staff password reset;
- Owner/Manager needs an authorised password-reset action;
- the reset must be server-side and auditable as appropriate;
- a user must not reset staff belonging to another tenant;
- do not expose plaintext passwords in logs or responses.

The exact UI flow (set temporary password, force reset at next login, or admin-defined new password) is not specified. Confirm the preferred reset behaviour.

## 6. Authentication and Login

### 6.1 Explicit requirement

The PDF requires login with email and password.

### 6.2 Expected behaviour

- staff enters email and password;
- system authenticates the user;
- system identifies the correct tenant according to the approved architecture;
- user receives only the role/outlet permissions assigned to them;
- inactive/deactivated account or tenant cannot access protected functionality.

The PDF says the tenant is known from the login and no subdomains are used in the MVP. The audited repository currently describes host-based tenant resolution. That difference needs an explicit architecture decision, as noted in Section 3.2.

### 6.3 Server-side authorisation

The PDF's technical rules require permissions checked on the API, not only hidden in the UI.

Therefore:
- frontend route guards are usability controls, not the security boundary;
- every protected endpoint verifies authentication;
- every endpoint verifies role permission;
- outlet-scoped operations verify staff outlet access;
- tenant context is validated before retrieving tenant data.

### 6.4 Session and security design

The PDF does not prescribe JWT, session-cookie or token technology. Reuse the current repository's approved authentication approach.

Recommended safeguards:
- secure password hashing;
- secure session/token handling;
- login throttling and abuse protection;
- account deactivation enforcement;
- no secret/password logging;
- tests for cross-tenant authentication leakage.

These are implementation recommendations, not extra PDF requirements.

## 7. Building Management

### 7.1 Explicit fields

Every Building must have:
- type;
- altitude;
- map position.

The PDF describes a Building as a place in the complex. Every building is also a weather point.

### 7.2 Example buildings from the PDF

The sample demo complex contains:

| Building | Example altitude | Example activities |
|---|---:|---|
| Valley station | 1,050 m | Parking, bus/train stop, ticket desk, rental shop, cable car lower end |
| Main hotel | 1,200 m | Rooms, reception, restaurant, sauna, thermal bath, spa, game room, gym |
| Chalets | 1,250 m | Family chalets |
| Mid station | 1,800 m | Self-service restaurant, ski school meeting point, chairlift start |
| Mountain hut | 2,100 m | Dormitory beds, simple meals, guided hikes |
| Summit station | 2,600 m | Panorama restaurant, viewing platform, cable car top |

These are sample seed-data examples, not a fixed building list for every tenant.

### 7.3 Required behaviour

- create a building;
- define its type;
- record altitude;
- record map coordinates/position;
- update building details;
- associate outlets with the building;
- expose building location information to modules such as Hotel, Transport, Lifts and Weather.

### 7.4 Recommended data fields

| Field | Purpose | Source status |
|---|---|---|
| `name` | Building name | Needed to identify building |
| `building_type` | Type of building | PDF requirement |
| `altitude` | Elevation | PDF requirement |
| `latitude` / `longitude` or map position | Map location | PDF requirement |
| `is_active` | Hide retired building from new operations | Recommendation |
| `description` | Optional description | Recommendation |

The PDF does not prescribe exact building types, coordinate format, map library or whether building types are free text or controlled choices.

## 8. Outlet Management

### 8.1 Definition

The PDF defines an Outlet as a business unit in a building. Examples include:
- restaurant;
- hut kitchen;
- shop;
- rental;
- spa;
- sports;
- transport;
- lifts.

Each outlet has its own products, staff and reports.

### 8.2 Required behaviour

- create an outlet inside a building;
- identify its outlet type;
- maintain the outlet;
- associate products/menus where relevant;
- assign staff outlet access;
- make the outlet available to transaction/reporting modules.

### 8.3 Outlet and financial attribution

The PDF's money-flow rule requires each charge to record its outlet, tax and source. Outlet revenue is read from charge lines and reports split sales by outlet.

Therefore, outlet identity is not merely a UI grouping. It must flow through POS, rental/service/transport billing and Folio charge records as applicable.

### 8.4 Recommended fields

| Field | Purpose | Source status |
|---|---|---|
| `building` | Parent building | PDF relationship |
| `name` | Outlet display name | Needed to identify outlet |
| `outlet_type` | Restaurant, shop, rental etc. | PDF examples |
| `is_active` | Operational availability | Recommendation |
| `description` | Optional details | Recommendation |

The PDF does not define outlet opening hours or a full cost-centre/accounting hierarchy.

## 9. Season Management

### 9.1 Explicit requirement

Seasons have dates. The PDF gives examples:
- Winter;
- Summer;
- Closed.

Prices, menus and services can differ per season.

### 9.2 Required behaviour

- create a season;
- define its start/end dates;
- identify season type/name;
- make the active season available to pricing, POS menus, rental pricing, service schedules and reporting logic;
- support a closed period where applicable.

### 9.3 Recommended validation

- end date must not precede start date;
- prevent ambiguous overlapping seasons unless the client explicitly permits them;
- resolve the applicable season using tenant-local date;
- preserve historical pricing references for transactions already made.

The PDF does not specify how season overlaps, gaps or cross-season reservations are handled. These require consistent cross-module rules.

## 10. Tenant Settings

The PDF explicitly lists these settings:

| Setting | Required purpose |
|---|---|
| Currency | Tenant's configured currency |
| Tax rates | Tax configuration used by sales/charges |
| Timezone | Tenant-local dates, scheduling and reporting |
| Temperature units | °C or °F |
| Wind units | km/h or mph |

### 10.1 Currency

Currency is a tenant setting in the MVP. Financial records and report displays must use the configured currency and follow the technical rule for integer minor units.

Do not infer that the MVP supports multiple transaction currencies. Multi-currency appears in Section 9 as a future post-MVP feature.

### 10.2 Tax rates

Tax rates are tenant-configurable and must be available to the financial/POS modules.

The PDF does not define:
- tax jurisdictions;
- compound taxes;
- inclusive vs exclusive tax pricing;
- tax exemptions;
- tax reporting format.

Do not invent tax policy. Implement the configured rates and align exact calculation behaviour with Module C and the client.

### 10.3 Timezone

Tenant timezone must govern:
- login/session context where relevant;
- date-based reservations;
- “today” dashboards;
- nightly posting;
- weather fetch presentation;
- season selection;
- reports and date filters;
- scheduled transport/services.

Do not rely on server-local time for business dates.

### 10.4 Measurement units

The PDF specifies:
- temperature: °C or °F;
- wind: km/h or mph.

These settings must be consistently used on weather dashboards and relevant operational views.

## 11. Audit Logging

### 11.1 Explicit requirement

The PDF requires an audit log for every:
- charge;
- payment;
- void.

The Core Concepts section further explains that a charge/payment is never edited or deleted; it is voided with reason, user and timestamp.

### 11.2 Required audit information

The financial records should be able to identify:
- event type (charge, payment or void);
- affected record/reference;
- outlet/source where applicable;
- actor/user;
- timestamp;
- void reason where relevant.

The exact schema and retention policy are not specified. The audit event must be reliable and cannot be silently removed through normal application operations.

### 11.3 Shared financial ownership

Although the audit-log requirement is listed in Module A, the actual Charge and Payment entities belong to Module C's Folio/financial domain.

Recommended design:
- Module C owns authoritative charge/payment/void operations;
- an audit mechanism records these events consistently;
- Module A provides common audit infrastructure or configuration as appropriate;
- other modules call the shared financial services rather than writing money records directly.

Do not implement multiple independent audit logs that disagree about the same charge.

### 11.4 Append-only rule

The PDF's hard rule:

- a charge is never edited or deleted;
- a payment is never edited or deleted;
- a void records the reason, user and timestamp.

This rule must be enforced by backend service/API logic, not just by hiding edit/delete buttons in the UI.

## 12. Recommended Role and Permission Matrix

The PDF defines roles and main jobs but does not provide a complete permission matrix. The following is an implementation guide that must be reconciled with the client's final access policy.

| Capability | Super Admin | Owner | Manager | Operational Staff |
|---|---|---|---|---|
| Create complex | Yes | No | No | No |
| Create complex Owner | Yes | No | No | No |
| Activate/deactivate complex | Yes | No | No | No |
| Manage tenant settings | No, except platform-level setup | Yes | No unless delegated | No |
| Manage buildings/outlets | No, except platform provisioning | Yes | No unless delegated | No |
| Manage staff/roles | Platform account creation as needed | Yes | Staff password reset | No |
| Assign outlet access | No tenant operational access | Yes | Only if explicitly delegated | No |
| Manage tenant seasons | No | Yes | No unless delegated | No |
| Perform module operations | No tenant-level operational work | Yes | Assigned outlets | Assigned role/outlet |

The phrase “Owner: Everything” is in the PDF. Exact ability of Managers to create staff accounts, reset passwords and alter roles is not fully described. The PDF explicitly says Owners and Managers reset staff passwords themselves; do not infer all other staff-administration privileges from that one statement.

### 12.1 Operational role responsibilities

Use the PDF's role descriptions as the starting point:
- Front Desk: reservation, check-in/check-out, Folio and payment.
- Cashier: POS order/payment/charge to room.
- Rental Desk: rent-out/return/damage fee.
- Service Staff: slots/bookings/schedule/guided-outing headcount.
- Transport Desk: shuttles/taxi/pickups/vehicles/drivers.
- Lift Operator: phone-based lift status.

The corresponding module APIs must enforce these responsibilities.

## 13. Suggested Data Model

The names below are suggestions, not a prescribed database schema. The latest repository audit reports existing `Tenant`, `Domain`, `SaasUser`, `Staff`, `Building`, `Outlet`, `Season` and settings-related models. Inspect those before adding new models.

| Proposed/current concept | Responsibility |
|---|---|
| `Tenant` | One mountain complex in central SaaS registry |
| `Domain` | Host/domain mapping if retained by the approved architecture |
| `SaasUser` | Platform-level user/Super Admin identity according to current implementation |
| `Staff` | Tenant staff account |
| `Role` / role choices | Tenant role assignment |
| `StaffOutletAccess` | Staff access to permitted outlets |
| `Building` | Tenant building with type, altitude and map position |
| `Outlet` | Business unit inside a building |
| `Season` | Named season with dates |
| `TenantSettings` | Currency, tax, timezone and units |
| `AuditEvent` or financial audit mechanism | Charge/payment/void audit event, if centralised |

### Relationship overview

```text
Platform / Central SaaS
 ├── Tenant
 ├── Super Admin identity
 └── Domain mapping (only if approved for MVP architecture)

Tenant context
 ├── Owner
 ├── Staff
 │    ├── Role
 │    └── Outlet Access
 │
 ├── Building
 │    └── Outlet
 │
 ├── Season
 ├── Tenant Settings
 │    ├── Currency
 │    ├── Tax Rates
 │    ├── Timezone
 │    └── Temperature/Wind Units
 │
 └── Financial audit events
      ├── Charge (Module C)
      ├── Payment (Module C)
      └── Void (Module C)
```

### Important tenancy note

The model diagram deliberately shows central SaaS identity/tenant registry separately from tenant-owned operational records, because that is how the current repository audit describes the DB-per-tenant implementation. However, the PDF's `tenant_id` requirement/technical wording must be reconciled with the current implementation before treating this diagram as the final approved architecture.

## 14. Suggested API Capability Map

The PDF does not prescribe API URL paths. The following are proposed DRF capabilities, not a claim that every endpoint already exists.

### 14.1 SaaS / Super Admin APIs

| Capability | Purpose |
|---|---|
| Super Admin login | Authenticate platform-level administrator |
| Tenant list | List complexes in the central registry |
| Tenant create | Create/register a complex |
| Owner create | Create initial Owner account for the complex |
| Tenant activate | Activate a complex |
| Tenant deactivate | Deactivate a complex |
| Tenant detail | View complex registration/status information |

### 14.2 Tenant Core APIs

| Capability | Purpose |
|---|---|
| Staff login | Email/password login |
| Staff list/detail | Manage tenant staff |
| Staff create/update | Create or update staff account |
| Staff role assignment | Assign Owner/Manager/operational role as permitted |
| Staff outlet access | Assign permitted outlet(s) |
| Staff password reset | Owner/Manager reset staff password without email |
| Building CRUD | Manage name/type/altitude/map position |
| Outlet CRUD | Manage outlet inside building |
| Season CRUD | Manage season and date range |
| Tenant settings | Read/update currency, tax, timezone and units |
| Financial audit read | Retrieve authorised charge/payment/void audit history |

### 14.3 API rules

- Enforce authentication and permissions on every protected endpoint.
- Enforce platform-level Super Admin permissions separately from tenant Owner permissions.
- Resolve tenant context through the architecture approved by the client.
- Prevent client-supplied tenant IDs/hostnames from bypassing tenant isolation.
- Enforce staff outlet access on the server.
- Ensure deactivated tenant/staff access is blocked.
- Keep password reset and activation actions auditable.
- Do not expose secrets or password hashes in API responses.

## 15. Frontend Screens

Suggested screens based on the PDF's responsibilities:

### Super Admin
1. Super Admin login.
2. Tenant/Complex list.
3. Create Complex form.
4. Create Owner account form/step.
5. Tenant detail.
6. Activate/Deactivate action with status history (history is a recommendation).

### Owner / Core Settings
7. Owner dashboard entry point.
8. Building list.
9. Building create/edit with type, altitude and map position.
10. Outlet list by building.
11. Outlet create/edit.
12. Staff list.
13. Staff create/edit.
14. Role assignment.
15. Outlet access assignment.
16. Staff password reset.
17. Season list and date form.
18. Tenant settings (currency/tax/timezone/units).
19. Financial audit log view, with permissions appropriate to the Owner/Manager policy.

These are recommended screens. The PDF does not provide wireframes or a specific UI component library.

## 16. Existing Repository Audit Findings

The Project Audit Report dated 3 November 2026 identifies Module A as the strongest/most implemented area.

### 16.1 Reported strengths

| Area | Audit finding |
|---|---|
| Overall Module A | Strongest area |
| Authentication | Present; staff login E2E verified in prior work item |
| Buildings | Core API present |
| Outlets | Core API present |
| Seasons | Core API present |
| Settings | Core API present |
| Staff | Core API present |
| Outlet access | Core API present |
| SaaS registration/activation | Present |
| Tenant provisioning services | Present in SaaS layer |
| Isolation tests | Test code exists |
| Tenant E2E login | Verified in prior work item 26 |

### 16.2 Gaps and qualifications

The audit also notes:
- tenant isolation tests exist, but the full automated suite was not re-run during that audit;
- seed buildings are partial;
- the current audit does not independently prove every Module A acceptance criterion;
- the audit's repository architecture uses central SaaS DB plus tenant DBs;
- the frontend remains a shell/template in places, and module workflows are not fully pilot-ready;
- B–I domain ViewSets have an authentication configuration defect, while Core/SaaS ViewSets mostly use the correct staff authentication.

### 16.3 Important audit interpretation

“Strongest area” does not mean Module A is fully accepted. The client acceptance test still requires two complexes on the same server to be isolated, proven by an automated test.

Also, the repository audit says the SaaS register/activate feature is present. It does not mean that every possible onboarding, domain, email or subscription feature exists. Self-signup/subscriptions are listed by the client in Post-MVP, not in the current MVP.

## 17. Recommended Development Sequence

Because much of Module A already exists, start with an audit/verification pass rather than rebuilding it.

| Step | Task | Completion evidence |
|---:|---|---|
| 0 | Read existing tenancy/auth/core/SaaS code and architecture docs | Current architecture and PDF differences documented |
| 1 | Resolve `tenant_id` vs DB-per-tenant and login-vs-host resolution decisions | Client/project owner approves the tenancy model for MVP |
| 2 | Verify Super Admin authentication and permissions | Only platform Super Admin can create/activate/deactivate tenants |
| 3 | Verify tenant creation and Owner creation | New complex has correct Owner association |
| 4 | Verify tenant activation/deactivation | Active/inactive access behaves as approved; no data deletion |
| 5 | Verify tenant staff login | Email/password login binds user to correct tenant |
| 6 | Verify roles and outlet access | API enforces role and outlet access |
| 7 | Verify password-reset flow | Owner/Manager can reset staff passwords without email |
| 8 | Verify Building management | Type, altitude and map position persist correctly |
| 9 | Verify Outlet management | Outlets belong to the correct building and tenant |
| 10 | Verify Season management | Season dates and applicable season resolution work |
| 11 | Verify tenant settings | Currency, tax rates, timezone and units are applied consistently |
| 12 | Verify financial audit logging | Every charge/payment/void event is recorded |
| 13 | Complete tenant isolation automated tests | Two tenants cannot read/write each other's data |
| 14 | Complete seed/demo tenant setup | Sample complex includes buildings/outlets/seasons/settings |
| 15 | End-to-end regression | Super Admin creates two complexes; each Owner/staff works only in own complex |

## 18. Automated Test Scenarios

### 18.1 Super Admin tests

- A platform Super Admin can authenticate.
- A tenant Owner cannot access Super Admin tenant-creation operations.
- Super Admin can create a complex.
- Owner account is created/associated with the correct tenant.
- Super Admin can activate/deactivate a complex.
- Deactivated tenant access follows the approved policy.
- Deactivation does not unexpectedly delete tenant data.

### 18.2 Authentication tests

- Valid email/password login succeeds.
- Invalid credentials fail safely.
- Inactive staff cannot log in.
- Staff receives the correct tenant context.
- Login for Tenant A cannot resolve Tenant B context.
- Password reset does not require email sending.
- Owner/Manager password reset is restricted to authorised staff within their tenant.

### 18.3 Role and outlet-access tests

- Owner can perform the authorised tenant administration actions.
- Manager access is limited to assigned outlets and permissions.
- Cashier cannot access unassigned outlet operations.
- Rental Desk cannot perform unrelated staff administration.
- Lift Operator can access only the approved lift status operation.
- API rejects unauthorised operations even if frontend buttons are manually invoked.

### 18.4 Building and outlet tests

- Building requires type, altitude and map position.
- Building belongs to the correct tenant.
- Outlet belongs to a building in the same tenant.
- Outlet cannot be attached to a building from another tenant.
- Staff outlet access cannot reference another tenant's outlet.

### 18.5 Season and settings tests

- Season has valid dates.
- Invalid date range is rejected.
- Season resolution uses tenant-local date.
- Tenant currency is applied to financial displays.
- Tenant tax rates are available to sales/charges.
- Tenant timezone is used for “today” and scheduled operations.
- Temperature unit is °C or °F according to settings.
- Wind unit is km/h or mph according to settings.

### 18.6 Financial audit tests

- Every charge creation produces the required audit record.
- Every payment creation produces the required audit record.
- Every void records reason, actor and timestamp.
- Charge/payment cannot be physically edited or deleted through normal APIs.
- Audit records cannot be changed by unauthorised staff.

### 18.7 Tenant isolation acceptance test

This is the principal client acceptance test.

**Setup:**
1. Create Tenant A and Owner A.
2. Create Tenant B and Owner B.
3. Add separate buildings, outlets and staff to each tenant.
4. Authenticate as Owner A and Staff A.
5. Authenticate as Owner B and Staff B.

**Assertions:**
- Tenant A users see only Tenant A data.
- Tenant B users see only Tenant B data.
- Tenant A cannot retrieve Tenant B records by guessing/changing IDs.
- Tenant A cannot modify or delete Tenant B records.
- Tenant A cannot assign Tenant B's outlets to its own staff.
- Host/header/request tampering cannot switch tenant context.
- Super Admin platform operations remain separate from tenant Owner operations.

The test must run automatically in the test suite. Manual UI checks alone do not meet the client's “Done when” condition.

## 19. Client / Project Owner Decisions to Resolve

The supplied PDF and audit have some details that need explicit agreement:

1. Does the client approve the current DB-per-tenant implementation even though the PDF Core Concepts says every table carries `tenant_id`?  **RESOLVED (2026-11-03, MVP freeze, item 29)** — see `docs/DECISIONS.md` (2026-11-02 Accepted + 2026-11-03 freeze row). DB-per-tenant is the MVP isolation story; no `tenant_id` columns; active-connection isolation.
2. How should “tenant known from login, no subdomains in MVP” align with the audit's central/tenant host routing?  **RESOLVED (2026-11-03, MVP freeze, item 29)** — host routing satisfies "tenant from login": no subdomain purchase needed (shared VPS hostname/sslip.io or IP:port); `Domain` registry maps host → tenant; staff session is bound to the issuing tenant. Frontend never sends `tenant_id`; the host decides.
3. What exact complex fields are mandatory at creation (name, contact, country, address, timezone, etc.)?
4. Is a newly created complex active immediately or does Super Admin activate it separately?
5. What should a deactivated tenant's Owner/staff see when trying to log in?
6. Can tenant deactivation be temporary/reactivated without data loss?
7. What exact staff fields are required?
8. Can Managers create staff accounts, or only reset staff passwords?
9. Which role/outlet permission combinations are valid?
10. Does Owner have access to every outlet by default?
11. Can one staff user be assigned to multiple outlets?
12. Can one building contain multiple outlet types or repeated outlets of the same type?
13. What are the complete building types and outlet types, or should both be tenant-configurable?
14. Can seasons overlap? How should gaps/closed periods work?
15. Are tax rates configured by percentage only, or do multiple tax components/exemptions apply?
16. Which currencies and unit defaults should a newly created tenant receive?
17. Who can view the financial audit log?
18. What is the retention policy for audit logs?
19. Which approved tenant-isolation strategy should be treated as the source of truth for future developers?

## 20. Definition of Done

Module 1 should not be marked complete until:

- Staff login with email and password works.
- Owners and Managers can reset staff passwords without email sending.
- Super Admin can create a complex and its Owner account.
- Super Admin can activate/deactivate a complex.
- Building management supports type, altitude and map position.
- Outlets can be created inside buildings.
- Staff accounts have roles and outlet access.
- Season records support dates.
- Tenant settings support currency, tax rates, timezone and temperature/wind units.
- Financial audit log records every charge, payment and void.
- API permission checks enforce roles and outlet access.
- The approved tenant-isolation architecture is documented and implemented consistently.
- Automated isolation test proves two complexes on the same server cannot see each other's data.
- No tenant operation can cross into another tenant through identifier, host, header or API manipulation.
- The Super Admin and tenant Owner scopes remain separate.

## 21. Source References

- **Client MVP PDF:** *Mountain Tourism Complex Platform — MVP Scope v1*, Sections 2–5, pages 1–3. These define the sample complex, users/roles, core concepts, authentication, Super Admin, buildings, outlets, staff, seasons, settings and financial audit log.
- **Client MVP PDF:** Technical rules, Section 7, page 7. These define tenant isolation, API-side permissions, integer minor units and tenant timezone/currency/units.
- **Client MVP PDF:** Hosting/CI/CD, Section 6, pages 6–7. This defines the MVP VPS/deployment boundary and explicitly says there is no purchased domain at this stage; an sslip.io hostname or separate port is acceptable.
- **Project audit:** *Project Audit Report — Mountain Tourism Platform*, dated 3 November 2026, sections 2, 3, 5, 6 and 7. These report the current central SaaS/tenant DB architecture, Module A implementation status, core APIs, staff login verification, isolation test existence and remaining verification points.

---

**Implementation principle:** Do not rebuild Module A blindly. Audit and verify the existing SaaS, tenancy, authentication and core configuration code first. Resolve the documented tenancy-architecture discrepancy with the client/project owner, then complete the remaining acceptance tests. Module A is the foundation for every other module, so tenant isolation and API permissions must be proven before relying on the rest of the platform.
