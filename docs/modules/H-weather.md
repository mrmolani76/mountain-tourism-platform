# Module H — Weather

- **Phase**: 4 core (fetch/limits/warnings/cancel) + Phase 5 (lobby page, avalanche entry)
- **Full embed**: [`work/phases/04-transport-lifts-weather.md`](../../work/phases/04-transport-lifts-weather.md) + [`05-dashboard-and-handover.md`](../../work/phases/05-dashboard-and-handover.md)
- **PDF**: §5H
- **Depends on**: A (buildings as points), C (voids), G/F targets for warnings

## Purpose
Server-side MET Norway cache; limits; warnings on lifts/trips/outdoor slots; explicit cancel-for-weather; avalanche + lobby in Phase 5.

## Rules
- Server-only fetch; shared cache; User-Agent + caching policy
- Stale-ok if API down
- Never auto-cancel
- Void reason `weather`
- Credits: MET Norway (+ OSM on maps)

## Done when
Lowering limit warns matching targets; weather cancel leaves folios correct.

## Package
`apps.weather`
