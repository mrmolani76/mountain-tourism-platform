# MVP Scope Audit

- **Audit date**: 2026-11-26
- **Contract**: [Mountain Tourism MVP Scope v1](../document/Mountain_MVP_Scope_v1.pdf)
- **Repository reviewed**: Laravel API, Nuxt frontend, tenant migrations, tests, CI and delivery docs

## Executive assessment

The project has broad product implementation across Modules A-I. Core APIs, tenant modules, and staff-facing pages exist. That is meaningful progress, but implementation presence is not acceptance.

**The MVP is not ready for handover.** No module can be marked fully accepted from this audit: the acceptance flows were not run end-to-end, the current CI explicitly does not run tests, and concrete scope gaps remain. Six of the nine modules, D-I, have no module feature test files in the current Laravel test suite. A and B have relevant tests, but they were not run during this documentation audit.

Instead of an unsupported completion percentage:

- **Code coverage**: broad, with API and UI work present across the product modules.
- **Acceptance evidence**: incomplete. The only recent browser evidence available is a partial Hotel route check, not a full workflow pass.
- **Handover readiness**: blocked by scope discrepancies, unscheduled background work, missing acceptance proof, and stale or missing operations documentation.

Status labels below mean:

- **Implemented, unverified**: code exists, but acceptance evidence is missing.
- **Partial**: code exists, with a specific contract gap or missing acceptance gate.
- **Missing**: no implementation was found for the stated requirement.

## Module-by-module status

| Module | Status | Evidence and remaining work |
|---|---|---|
| **A. Core and super admin** | Partial | SaaS tenant/domain APIs, tenant connection resolution, staff, role/access support, buildings, outlets, seasons and settings exist. See [SaaS routes](../backend-laravel/Modules/Saas/routes/api.php) and [Core settings](../backend-laravel/Modules/Core/App/Http/Controllers/SettingsController.php). No super-admin tenant-management page was found in the Nuxt pages. The PDF's `tenant_id` model conflicts with the implemented DB-per-tenant design. VPS deployment and test-running CI are not delivered. |
| **B. Hotel, chalets and huts** | Partial | Rooms, room types, board plans, reservations, accommodation allocation, stays, check-in/out, pricing and nightly posting are implemented. The [Hotel module feature test](../backend-laravel/tests/Feature/Modules/Hotel/HotelModuleTest.php) covers multi-night half-board posting and checkout/folio behavior, but was not run in this audit. [`hotel:nightly-posting`](../backend-laravel/Modules/Hotel/App/Console/NightlyPostingCommand.php) exists as a command, but no scheduler registration was found. The [posting service](../backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php) and dashboard use the application date, not the tenant's configured timezone. |
| **C. Guest folio and checkout** | Partial | Folio, integer-cent charges/payments, split payments, void entries, charge-to-room and invoice payload/UI exist. New charge writes require an active tenant outlet; room nights map to the building's Reception, hut beds to its Hut Kitchen, and board charges to the first accommodation allocation's outlet. Legacy null-attributed ledger rows remain unchanged. New restrictive foreign keys prevent Folio, charge-history, or outlet deletion from cascading through the ledger. Focused regression tests were added but are not yet verified. Generic voids default to `other`; explicit weather voids retain `weather` (BUG-MONEY-001 closed). |
| **D. Restaurants, huts and shop** | Implemented, unverified | POS product, shift, order, settlement, board-meal and folio integration code and staff pages exist. No POS module feature tests or browser acceptance evidence were found. |
| **E. Equipment rental** | Implemented, unverified | Equipment types/items, seasonal pricing, rental/return, late and damage flows exist. No Rental module feature tests or verified no-double-rent/late-day acceptance result were found. |
| **F. Hotel services and activities** | Partial | Service catalogue, schedule rules, slots, staff assignment, bookings, participants and outings exist. The [service schema](../backend-laravel/Modules/Services/Database/migrations/tenant/2026_11_13_000001_create_service_service_table.php) has no minimum-age field, although the PDF requires minimum age. No Services module feature tests or verified capacity/staff-conflict acceptance result were found. |
| **G. Transport and lifts** | Partial | Routes/stops, trips, vehicles, drivers, passengers, lift status history and passes exist. [Reservation integration](../backend-laravel/Modules/Transport/App/Services/TripService.php) creates a pickup from arrival details only; reservation/departure-to-dropoff support was not found. The [Routes UI](../frontend/pages/transport/routes/index.vue) only creates a route and optional first stop; it does not draw/edit path geometry on a map. No Transport module feature tests were found. |
| **H. Weather** | Partial | Weather points, forecast snapshots, limits, warnings, explicit cancellations and a [public lobby API](../backend-laravel/Modules/Weather/routes/api.php) exist. Fetch and evaluate are staff-triggered API actions; no hourly weather scheduler/job was found. [WeatherFetchService](../backend-laravel/Modules/Weather/App/Services/WeatherFetchService.php) uses MET Norway first, but Open-Meteo as a fallback not listed in the contract. The [lobby payload](../backend-laravel/Modules/Weather/App/Services/LobbyService.php) includes warnings and avalanche data, but not lift status; no public Nuxt lobby screen was found in [weather pages](../frontend/pages/weather). |
| **I. Owner dashboard and reports** | Partial | Today dashboard, date-range revenue/payment/weather-cancellation reports and CSV support exist. No Reports module feature tests were found. [DashboardService](../backend-laravel/Modules/Reports/App/Services/DashboardService.php) uses the application timezone, not `core_tenant_settings.timezone`, so tenant-local day boundaries are not satisfied. Totals have not been compared against seeded transactions in an executed test. |

## Cross-module relationship map

The main data flow is present in code, with database-per-tenant scoping as the active isolation boundary:

```text
Central SaaS Tenant -> Domain -> resolved tenant database
Tenant Core: Building -> Outlet; Staff -> role and outlet access

Hotel Reservation -> Board Plan + Room/Hut allocation -> Stay -> Folio
Stay -> Nightly Posting -> Folio Charges

POS Order / Rental / Service Booking / Transport sale
    -> Folio Charge (source_type + source_id; some source links are polymorphic)
Folio -> Charges + Payments + Void records

Service -> Schedule Rule -> Slot -> Staff assignments -> Booking/Participants
Rental Type -> Seasonal Price + Equipment Item -> Rental -> Rental Items
Transport Route -> Stops -> Trip -> Passengers; Trip -> Vehicle/Driver
Lift -> Status History; Lift Pass Product -> POS Product -> issued pass

Weather Point -> Forecast Snapshot -> Forecast Values
Weather Limit -> Warning -> polymorphic affected target
Weather Cancellation -> target status + related Folio voids
Reports -> aggregate tenant-local operational, charge and payment data
```

### Relationship and ledger risks

1. **Folio attribution is not guaranteed.** `folio_charge.outlet_id` is nullable, and [StoreChargeRequest](../backend-laravel/Modules/Folio/App/Http/Requests/StoreChargeRequest.php)/[ChargeToRoomRequest](../backend-laravel/Modules/Folio/App/Http/Requests/ChargeToRoomRequest.php) allow null. [NightlyPostingService](../backend-laravel/Modules/Hotel/App/Services/NightlyPostingService.php) posts room and board charges without an outlet. The reports consequently include a “No outlet” revenue bucket, which falls short of the contract that each charge records its outlet.
2. **Historical money rows have cascading deletion paths.** `folio_charge.folio_id` and `folio_payment.folio_id` cascade on Folio deletion. Money is append-only by application convention, but the schema does not protect it from this delete path.
3. **Some cross-module links are soft references.** Charge `source_type`/`source_id`, `stay_id`, FolioVoid `charge_id`/`payment_id`, weather warning targets and cancellation targets are not all database-enforced foreign keys. Service validation must keep them consistent; the audit trail should be tested for deleted or invalid target cases.
4. **Transport departure relationship is incomplete.** Reservation stores arrival mode/time/station, and [TripService::pickupFromReservation()](../backend-laravel/Modules/Transport/App/Services/TripService.php) only creates pickup trips. The matching departure details/drop-off flow is not present.

## Contract and delivery gaps

### Product requirements

- **Tenancy contract conflict**: the PDF says every business table carries `tenant_id`; the project decision retains DB-per-tenant under Rule 3 and intentionally omits that column. The user delegated the pragmatic choice, recorded in [DECISIONS.md](DECISIONS.md), but the PDF is not amended. Record a formal client-facing amendment before calling Module A accepted.
- **Super-admin UI missing**: tenant registry APIs exist, but no tenant create/activate/deactivate page was found in [frontend/pages](../frontend/pages).
- **Minimum age missing**: Module F's service schema and UI do not expose the required minimum age.
- **Departures/drop-offs missing**: transport only builds a pickup trip from reservation arrival data.
- **Map requirement not met**: the [Transport Routes page](../frontend/pages/transport/routes/index.vue) does not edit geometry or render a route map. Separately, active location components use Google Maps Places/Geocoder ([LocationSelectorDialog.vue](../frontend/components/dialogs/LocationSelectorDialog.vue) and [google-map-loader.client.ts](../frontend/plugins/google-map-loader.client.ts)). OSM is selected per the PDF and [DECISIONS.md](DECISIONS.md); replace these workflows as part of the approved OSM implementation.
- **Weather lobby incomplete**: the public API has no matching public Nuxt screen and omits lift state, even though the Module G acceptance check requires a closed lift to appear on the lobby screen within one minute.
- **Weather source**: MET Norway only is selected per the PDF and [DECISIONS.md](DECISIONS.md). The existing Open-Meteo fallback remains to be removed in weather implementation work.
- **Tenant-local time missing from key flows**: settings store a timezone, but [`DateTimeZoneMutator::tenantTimezone()`](../backend-laravel/app/Services/DateTimeZoneMutator.php) reads `app.timezone`; hotel nightly posting and the Reports dashboard call `now()->toDateString()`. Tenant timezone must drive posting, reports and “today”.

### Testing and release evidence

- Current test classes are concentrated in SaaS/Core/Hotel. No module feature test files were found for POS, Rental, Services, Transport, Weather or Reports.
- [ci.yml](../.github/workflows/ci.yml) performs Composer validation, PHP syntax checks and an application boot check. It explicitly does **not** run tests. It also does not build/check the Nuxt frontend.
- [CI.md](CI.md) describes an older Django/Python/Postgres test workflow and claims tests run. It does not match the current Laravel workflow.
- Recent browser evidence is limited: 10 Hotel routes returned HTTP 200 and rendered headings; no forms were submitted. In the earlier 55-route pass, 18 routes timed out on the original heading-only wait. This is not a full menu or acceptance check.
- No OpenAPI/Swagger specification or generator was found in the current backend/docs search.

### Operations and handover

- [VPS_SETUP.md](VPS_SETUP.md) still gives Python/Django/Gunicorn commands and creates one `mountain` database, not the current Laravel + central DB + one DB per tenant arrangement.
- [.github/workflows](../.github/workflows) contains only the quality-check CI file; no deployment workflow/release rollback implementation was found.
- No tested nightly `pg_dump` backup/restore or five-release rollback evidence was found. The existing VPS backup example is for one database and has not been tested for all tenant databases.
- [FoundationSeeder](../backend-laravel/Modules/Core/Database/Seeders/FoundationSeeder.php) creates the six buildings and a limited set of outlets, not the complete demo complex with rooms, rates, services, rental/POS products, transport and weather data.
- [SEED_COMPLEX.md](SEED_COMPLEX.md) describes the layout but not a full seed procedure. [README.md](README.md) links to `HANDOVER.md`, but that file is absent.
- [ARCHITECTURE.md](ARCHITECTURE.md), [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md), [CI.md](CI.md), and [MILESTONES.md](MILESTONES.md) contain Django-era or deferred-work descriptions that do not consistently reflect the current Laravel + Nuxt implementation.
- No recorded handover walkthrough evidence was found.

## Recommended remaining work, in order

1. **Obtain formal tenancy contract amendment**: the project retains DB-per-tenant, while the PDF says `tenant_id`; do not call Module A accepted until the discrepancy is formally addressed.
2. **Close ledger integrity gaps**: require outlet attribution for every charge; prevent deletion paths from erasing charges/payments; add tests for source/void link integrity. BUG-MONEY-001 is closed.
3. **Implement and register tenant-time-aware schedules**: nightly posting and hourly weather fetch/evaluation, with failure visibility and idempotency checks.
4. **Finish explicit product gaps**: service minimum age, reservation departure/drop-off workflow, OSM route geometry/map editing, removal of the Open-Meteo fallback, and a public lobby screen that includes lift status and refresh behavior.
5. **Add module acceptance tests for D-I** and run the complete backend suite on an approved setup. Add frontend build/checks to the release gate and align CI docs with the actual workflow.
6. **Complete operational handover**: Laravel/tenant-aware VPS guide, deployment workflow, backup retention and tested restore, tested rollback, complete demo seed, OpenAPI documentation and recorded walkthrough.
7. **Reconcile docs and acceptance status**: update the schema/architecture/module docs only after decisions are approved; do not infer module completion from historical work-item labels.

## Audit method and limits

- Read all nine pages of the scope PDF and cross-checked module routes, services, models, migrations, frontend pages, tests and delivery documents.
- Read-only audit. No application code, database records or runtime settings were changed.
- No automated tests/builds were run. This report does not claim that unexecuted flows work.
- Browser status is the partial evidence noted above, not a full regression run.
