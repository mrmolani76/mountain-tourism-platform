# Phase plans (from MVP PDF)

Source: `document/Mountain_MVP_Scope_v1.pdf` (§5–§8, §11).  
Stack focus: **Django + DRF API only** until frontend is opened.

## Rule: phase file = complete module description

Har phase MD ke andar **us phase ke saare modules** full describe hain (purpose, actors, entities, rules, APIs, done-when, package).

| Phase | Modules fully described inside the phase file |
|-------|-----------------------------------------------|
| [01-foundation.md](01-foundation.md) | **A** Core and super admin |
| [02-hotel-and-folio.md](02-hotel-and-folio.md) | **B** Hotel + **C** Folio |
| [03-pos-rental-services.md](03-pos-rental-services.md) | **D** POS + **E** Rental + **F** Services |
| [04-transport-lifts-weather.md](04-transport-lifts-weather.md) | **G** Transport/lifts + **H** Weather core |
| [05-dashboard-and-handover.md](05-dashboard-and-handover.md) | **I** Reports + **H** lobby/avalanche + §11 handover |

Short cross-links also live under [`docs/modules/`](../../docs/modules/).

## Kanban

- Phase 1 backend packages: `work/todo/` IDs **02–10**
- Later phases: seed todos only when that phase starts (`work-tracker`)
- `milestone: N` on every work item

## Flow

```text
PDF §8 milestone
    → work/phases/0N-*.md  (modules complete inside)
    → work/todo/NN-*.md    (executable backend tasks)
    → apps.* implementation
```
