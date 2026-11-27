# Change Report: Work 107 — MVP Contract Decisions

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 107-mvp-contract-decisions

## 1. Summary
Recorded the user-delegated implementation choices for tenancy, mapping, and weather source, while preserving the formal PDF amendment requirement for tenancy.

## 2. Before
- The PDF says business tables carry `tenant_id`, while the established project architecture is DB-per-tenant.
- Google Maps components and an Open-Meteo fallback remain in code despite the PDF and technical rules specifying OSM and MET Norway.

## 3. After
- Recorded DB-per-tenant as the implementation choice under Rule 3, with a formal client-facing PDF amendment still required before Module A acceptance.
- Recorded OSM maps and MET Norway only as the selected providers; corresponding code changes remain for later implementation work.
- Updated the scope audit to reflect the decisions and closed folio void bug.
- No application code or contract PDF was changed.

## 4. Reason
The user delegated a pragmatic choice aligned with the project context. The decision must remain transparent about the unresolved PDF tenancy clause.

## 5. Files Affected
- `docs/DECISIONS.md`
- `docs/MVP_SCOPE_AUDIT.md`
- `work/completed/107-confirm-mvp-contract-decisions.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`
- `work/changes/2026-11-27--107-mvp-contract-decisions.md`

## 6. Verification / Status
- [x] `TECHNICAL_RULES.md`, `ARCHITECTURE.md`, and `DATABASE_SCHEMA.md` match the selected implementation choices.
- [x] Manual folder/frontmatter/PLAN validation passed.
- [x] Formal PDF amendment for DB-per-tenant remains explicitly pending before Module A acceptance.
