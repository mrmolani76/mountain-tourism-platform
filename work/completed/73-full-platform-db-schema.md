---
title: Full platform DB schema design (Modules A to I)
id: 73
priority: high
status: completed
milestone: 2
module: docs
area: docs
effort: L
dependencies: 72
bug: none
created: 2026-11-05
updated: 2026-11-05
---

# Full platform DB schema design (Modules A to I)

## What to do (What)
Design one database schema contract for the whole platform before building Modules D to I:
central SaaS database plus every tenant table for Modules A to I, with columns, keys,
indexes, foreign key actions and invariants.

## Current / Before
- No single schema document existed.
- Table design lived in scattered migration files and in
  `work/phases/Module_*_Deep_Analysis.md` prose.
- Module D to I had entity names only, no column level design.
- `docs/GLOSSARY.md` still claimed every business table carries `tenant_id`.

## Expected / After
- `docs/DATABASE_SCHEMA.md` is the schema contract, linked from `docs/README.md`.
- Implemented tables marked as implemented with their migration path.
- Proposed tables documented for Modules D to I.
- Cross module money flow, invariants, index plan and migration order written down.
- Open client questions listed with the current default.

## Why
Later modules must not re-invent the money model, the tenancy model or the
hotel to folio contract. One document keeps Module B to I consistent.

## Acceptance Criteria (complete only when all ticked)
- [x] Central database tables documented
- [x] Module A to I tenant tables documented
- [x] Money append-only rule and cross module charge flow documented
- [x] Invariants, index plan and migration order documented
- [x] Open questions listed with defaults
- [x] `docs/README.md` links the schema doc
- [x] `work/changes/2026-11-05--73-full-platform-db-schema.md` written
- [x] PLAN.md + SUMMARY.md updated
- [ ] Client answers on the 7 open questions (outside this item)

## Files Likely Affected
- `docs/DATABASE_SCHEMA.md`
- `docs/README.md`

## Notes / Follow-ups
- `docs/GLOSSARY.md` tenant row still says every table carries `tenant_id`. Needs a wording fix
  to match DB-per-tenant before client handover.
- No migration was created or run for proposed tables. Migrations land module by module.

---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-05
- **What was done**: Wrote `docs/DATABASE_SCHEMA.md` covering central SaaS, Module A Core,
  Module B Hotel, Module C Folio (implemented) and Modules D POS, E Rental, F Services,
  G Transport, H Weather, I Reports (proposed). Added invariants, index plan, migration order,
  money flow, menu note and open questions.
- **Files changed**: `docs/DATABASE_SCHEMA.md` (new), `docs/README.md`
- **Verification**: Existing Core, Hotel, Folio, Saas migrations inspected and reconciled with the
  phase deep analysis docs. Design only, no migration run.
- **Change report**: `work/changes/2026-11-05--73-full-platform-db-schema.md`
- **PLAN / SUMMARY**: updated yes
