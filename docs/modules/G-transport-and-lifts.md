# Module G — Transport and lifts

- **Phase**: 4 — full embed in [`work/phases/04-transport-lifts-weather.md`](../../work/phases/04-transport-lifts-weather.md)
- **PDF**: §5G
- **Depends on**: A (buildings), B (reservation arrival), C (pass charges)

## Purpose
Own transport trips, lift status/passes, external arrival → pickup. No driver login. No gate hardware.

## Groups
Own / Lifts / External — see phase file table.

## Rules
- Driver/vehicle no overlap; seat cap
- Train arrival on reservation → pickup trip
- Lift status history with reason; lobby-readable within ~1 min
- Bike/moto via Module E

## Done when
No driver double-book; pickup from train arrival; closed lift visible to lobby within a minute.

## Package
`modules.transport`
