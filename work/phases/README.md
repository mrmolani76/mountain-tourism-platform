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

- Executable work only under `work/todo/` → `inprogress/` → `completed/` (`NN-title.md`)
- Module deep specs live in `work/phases/Module_*.md` (not a separate phase todos folder)
- Historical Module A checklist: `work/todo/A-core-and-super-admin.md`
- Remaining Module 1 closeout: kanban **29–33**
- `milestone: N` on every work item

## Flow

```text
PDF §8 milestone
    → work/phases/0N-*.md + Module_*_Deep_Analysis.md
    → work/todo/NN-*.md    (executable backend tasks)
    → modules.* implementation
```
