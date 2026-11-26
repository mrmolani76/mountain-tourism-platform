---
title: Confirm unresolved MVP contract decisions
id: 107
priority: high
status: todo
milestone: 1
module: docs
area: docs
effort: S
dependencies: none
bug: none
created: 2026-11-26
updated: 2026-11-26
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
- [ ] Obtain and record client approval or rejection for DB-per-tenant versus `tenant_id`.
- [ ] Obtain and record approval or rejection for Google Maps versus OSM-based maps.
- [ ] Obtain and record approval or rejection for Open-Meteo fallback versus MET Norway only.
- [ ] Update the affected architecture/technical decision documents consistently.
- [ ] `work/changes/YYYY-MM-DD--107-<desc>.md` written.
- [ ] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `docs/DECISIONS.md`
- `docs/TECHNICAL_RULES.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE_SCHEMA.md`

## Notes / Follow-ups
- No implementation choice should be silently treated as approved.
- See [MVP scope audit](../../docs/MVP_SCOPE_AUDIT.md).

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**:
- **Date**:
- **What was done**:
- **Files changed**:
- **Verification**:
- **Change report**:
- **PLAN / SUMMARY**:
