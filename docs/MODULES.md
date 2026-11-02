# Modules A–I (index)

Contract source: `document/Mountain_MVP_Scope_v1.pdf` §5.

**Where to read the full description**

1. **By phase (complete module text embedded):** [`work/phases/`](../work/phases/)
2. **By module file:** [`docs/modules/`](modules/)

| Code | Name | Phase | Phase file | Module file |
|------|------|-------|------------|-------------|
| A | Core and super admin | 1 | [01-foundation](../work/phases/01-foundation.md) | [A](modules/A-core-and-super-admin.md) |
| B | Hotel, chalets and huts | 2 | [02-hotel-and-folio](../work/phases/02-hotel-and-folio.md) | [B](modules/B-hotel-chalets-huts.md) |
| C | Guest folio and checkout | 2 | [02-hotel-and-folio](../work/phases/02-hotel-and-folio.md) | [C](modules/C-guest-folio-and-checkout.md) |
| D | Restaurants, huts and shop | 3 | [03-pos-rental-services](../work/phases/03-pos-rental-services.md) | [D](modules/D-restaurants-huts-shop.md) |
| E | Equipment rental | 3 | [03-pos-rental-services](../work/phases/03-pos-rental-services.md) | [E](modules/E-equipment-rental.md) |
| F | Hotel services and activities | 3 | [03-pos-rental-services](../work/phases/03-pos-rental-services.md) | [F](modules/F-hotel-services-and-activities.md) |
| G | Transport and lifts | 4 | [04-transport-lifts-weather](../work/phases/04-transport-lifts-weather.md) | [G](modules/G-transport-and-lifts.md) |
| H | Weather | 4 core + 5 lobby/avalanche | [04](../work/phases/04-transport-lifts-weather.md) / [05](../work/phases/05-dashboard-and-handover.md) | [H](modules/H-weather.md) |
| I | Owner dashboard and reports | 5 | [05-dashboard-and-handover](../work/phases/05-dashboard-and-handover.md) | [I](modules/I-owner-dashboard-and-reports.md) |

## Done when (one-liners)

| Code | Done when |
|------|-----------|
| A | Two tenants cannot see each other’s data (automated test) |
| B | 3-night half-board posts 3+3 charges; checkout at zero |
| C | Charge/void/split pay; folio zero; outlet reports from charges |
| D | Included dinner + wine on room |
| E | No double rent; late return extra day |
| F | Capacity/staff conflicts; cancel removes charge; outing headcount |
| G | No driver double-book; train→pickup; lift closed on lobby ≤1 min |
| H | Limit→warnings; weather cancel folios correct |
| I | Report totals match charges+sales to the cent |
