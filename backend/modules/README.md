# Modules (EQMS / Complere-style)

Domain code lives here — same idea as Complere `Modules/{Name}/`.
Platform tenancy (Host resolve, DB connections) stays in `../tenancy/` (like Complere `app/`).

## Inventory

| Module | Alias | DB | Role |
|--------|-------|-----|------|
| Saas | `saas` | central | Tenant registry, Domain, SaasUser |
| Core | `core` | tenant | Staff, outlets, seasons, settings |
| Hotel | `hotel` | tenant | Stays, rooms, chalets, huts |
| Folio | `folio` | tenant | Charges, payments, checkout |
| Pos | `pos` | tenant | Restaurants, huts, shop |
| Rental | `rental` | tenant | Equipment rental |
| Services | `services` | tenant | Bookable services / slots |
| Transport | `transport` | tenant | Trips, lifts |
| Weather | `weather` | tenant | Limits / warnings |
| Reports | `reports` | tenant | Owner dashboard |

## Per-module layout (mirror of Complere App/ + routes + migrations)

```text
modules/<alias>/
  module.json          # name, alias, central flag
  apps.py
  models/              # Complere App/Models
  services/            # Complere App/Services
  serializers/         # Complere App/Data (DRF)
  views/               # Complere App/Http/Controllers (DRF)
  permissions/         # Complere App/Policies
  urls.py              # Complere routes/api.php
  migrations/          # tenant DB (saas = central only)
  auth.py              # optional (session backends)
```

## Rules

- Modules must not import concrete classes from sibling domain modules; use services/events/DTOs at boundaries. Core is the shared tenant foundation.
- SaaS routes only on central host (`/api/saas/`).
- Thin DRF views → `services/`.
- No SSO in MVP.
