---
title: Confirm unresolved MVP contract decisions
id: 107
priority: high
status: completed
milestone: 1
module: docs
area: docs
effort: S
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-27
---

# Confirm unresolved MVP contract decisions

## What to do (What)
Record written agreement on the implementation choices that conflict with the MVP scope before building dependent features.

## Current / Before
The PDF says business tables carry `tenant_id`, while this project uses DB-per-tenant. The PDF specifies OSM-based maps and MET Norway, while the repository uses Google Maps components and has an Open-Meteo fallback. These choices are documented internally but are not approved amendments to the PDF.

## Expected / After
An approved decision record states the accepted tenancy model, mapping provider, and weather provider/fallback policy. Any rejected implementation is listed for replacement.

## Why
The scope PDF is the contract. Internal architecture notes alone do not authorize a change to it.

## Acceptance Criteria (complete only when all ticked)
- [x] Record the user-delegated choice to retain DB-per-tenant under Rule 3; record that a formal PDF amendment is still required before Module A acceptance.
- [x] Record OSM-based maps as the selected provider per the PDF.
- [x] Record MET Norway only as the selected weather source per the PDF; remove the Open-Meteo fallback in later implementation work.
- [x] Confirm the affected architecture/technical documents consistently describe these choices.
- [x] `work/changes/2026-11-27--107-mvp-contract-decisions.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `docs/DECISIONS.md`
- `docs/TECHNICAL_RULES.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE_SCHEMA.md`

## Notes / Follow-ups
- No implementation choice should be silently treated as approved.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).
- User delegated the choice to the agent when unavailable. The project keeps DB-per-tenant, uses OSM, and uses MET Norway only.
- This decision does not amend the PDF. A formal client-facing amendment for the tenancy clause remains required before Module A acceptance.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Recorded the user-delegated choices to retain DB-per-tenant, use OSM maps, and use MET Norway only. Documented that the PDF tenancy wording still needs a formal client-facing amendment.
- **Files changed**: `docs/DECISIONS.md`, `docs/MVP_SCOPE_AUDIT.md`, this work item, `work/PLAN.md`, `work/SUMMARY.md`, and `work/MEMORY.md`.
- **Verification**: Confirmed `TECHNICAL_RULES.md`, `ARCHITECTURE.md`, and `DATABASE_SCHEMA.md` are consistent with the selected implementation choices. Manual Kanban check passed.
- **Change report**: `work/changes/2026-11-27--107-mvp-contract-decisions.md`
- **PLAN / SUMMARY**: Item 107 moved to Completed.
