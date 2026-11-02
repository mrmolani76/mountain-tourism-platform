# Module F — Hotel services and activities

- **Phase**: 3 — full embed in [`work/phases/03-pos-rental-services.md`](../../work/phases/03-pos-rental-services.md)
- **PDF**: §5F
- **Depends on**: A, C; outdoor ↔ H warnings

## Purpose
Single booking engine for all service types. Slots, staff assignment, capacity, guided outing headcount.

## Rules
- Config-driven services only (no per-type apps)
- Capacity + staff conflict checks
- Cancel → void/remove charge
- Outing open until all participants back
- Outdoor slots get weather warnings (H)

## Done when
No over-capacity; no instructor double-book; cancel removes charge; missing participant blocks close.

## Package
`apps.services`
