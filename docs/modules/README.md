# Module deep-dives (backend / API)

Source: `document/Mountain_MVP_Scope_v1.pdf` §3–§5.  
Summary index: [`../MODULES.md`](../MODULES.md).

**Important:** The **complete** module description for implementation lives inside the **phase** file that owns that module (`work/phases/0N-*.md`). Files here are the per-module entry points + package notes; Phase 1 Module A is fully detailed both here and in the phase file.

**Delivery focus:** Django + DRF APIs, services, jobs, tests. UI deferred.

## Index

| Code | Module | Phase | File |
|------|--------|-------|------|
| A | Core and super admin | 1 | [A-core-and-super-admin.md](A-core-and-super-admin.md) |
| B | Hotel, chalets and huts | 2 | [B-hotel-chalets-huts.md](B-hotel-chalets-huts.md) |
| C | Guest folio and checkout | 2 | [C-guest-folio-and-checkout.md](C-guest-folio-and-checkout.md) |
| D | Restaurants, huts and shop | 3 | [D-restaurants-huts-shop.md](D-restaurants-huts-shop.md) |
| E | Equipment rental | 3 | [E-equipment-rental.md](E-equipment-rental.md) |
| F | Hotel services and activities | 3 | [F-hotel-services-and-activities.md](F-hotel-services-and-activities.md) |
| G | Transport and lifts | 4 | [G-transport-and-lifts.md](G-transport-and-lifts.md) |
| H | Weather | 4–5 | [H-weather.md](H-weather.md) |
| I | Owner dashboard and reports | 5 | [I-owner-dashboard-and-reports.md](I-owner-dashboard-and-reports.md) |

## Cross-cutting (read with every money module)

- Money append-only: Charge/Payment never edited/deleted; void with reason, user, timestamp
- Amounts in **integer minor units** (cents)
- Every business row: `tenant_id`; isolation in **one** backend layer
- Permissions on API; roles in [`../ROLES.md`](../ROLES.md)
- Vocabulary: [`../GLOSSARY.md`](../GLOSSARY.md)

## Suggested Django app layout (backend)

```text
backend/
  apps/
    core/          # A — tenant, building, outlet, season, staff, settings, audit
    hotel/         # B — rooms, board, reservations, stays, nightly posting
    folio/         # C — folio, charge, payment, void, checkout, invoice
    pos/           # D — products, orders, shifts, packed lunches
    rental/        # E
    services/      # F — service, slot, booking, outing
    transport/     # G — trips, lifts, passes
    weather/       # H
    reports/       # I
```

Names can shift during item 02 architecture, but keep domain words stable.
