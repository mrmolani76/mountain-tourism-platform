# Module deep-dives (backend / API)

Source: `document/Mountain_MVP_Scope_v1.pdf` §3–§5.  
Summary index: [`../MODULES.md`](../MODULES.md).

**Important:** The **complete** module description for implementation lives inside the **phase** file that owns that module (`work/phases/0N-*.md`). Files here are the per-module entry points + package notes.

**Delivery focus:** Django + DRF APIs, services, jobs, tests. UI deferred. SSO out of scope.

## Index

| Code | Module | Phase | File | Backend package |
|------|--------|-------|------|-----------------|
| A | Core and super admin | 1 | [A-core-and-super-admin.md](A-core-and-super-admin.md) | `modules.core` + central `modules.saas` |
| B | Hotel, chalets and huts | 2 | [B-hotel-chalets-huts.md](B-hotel-chalets-huts.md) | `modules.hotel` |
| C | Guest folio and checkout | 2 | [C-guest-folio-and-checkout.md](C-guest-folio-and-checkout.md) | `modules.folio` |
| D | Restaurants, huts and shop | 3 | [D-restaurants-huts-shop.md](D-restaurants-huts-shop.md) | `modules.pos` |
| E | Equipment rental | 3 | [E-equipment-rental.md](E-equipment-rental.md) | `modules.rental` |
| F | Hotel services and activities | 3 | [F-hotel-services-and-activities.md](F-hotel-services-and-activities.md) | `modules.services` |
| G | Transport and lifts | 4 | [G-transport-and-lifts.md](G-transport-and-lifts.md) | `modules.transport` |
| H | Weather | 4–5 | [H-weather.md](H-weather.md) | `modules.weather` |
| I | Owner dashboard and reports | 5 | [I-owner-dashboard-and-reports.md](I-owner-dashboard-and-reports.md) | `modules.reports` |

## Cross-cutting (read with every money module)

- Money append-only: Charge/Payment never edited/deleted; void with reason, user, timestamp
- Amounts in **integer minor units** (cents)
- **DB-per-tenant:** isolation is the active tenant database (`tenancy/`), not a `tenant_id` column on every row
- Permissions on API; roles in [`../ROLES.md`](../ROLES.md)
- Vocabulary: [`../GLOSSARY.md`](../GLOSSARY.md)
- Declared Python packages: [`../../backend/DEPENDENCIES.md`](../../backend/DEPENDENCIES.md) (no install until setup is opened)

## Backend layout (Complere-style modules)

```text
backend/
  tenancy/           # Host resolve, DB connections, provision stubs
  modules/
    saas/            # central registry (Tenant, Domain, SaasUser)
    core/            # A — staff, outlets, seasons, settings, audit stub
    hotel/           # B
    folio/           # C
    pos/             # D
    rental/          # E
    services/        # F
    transport/       # G
    weather/         # H
    reports/         # I
  config/
  tests/
```

Each module: `models/`, `services/`, `serializers/` (DRF DTOs), `views/`, `permissions/`, `urls.py`, `migrations/`, `module.json`.  
See [`../../backend/modules/README.md`](../../backend/modules/README.md).
