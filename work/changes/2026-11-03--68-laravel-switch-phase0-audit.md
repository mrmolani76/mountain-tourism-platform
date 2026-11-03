# Change Report: Laravel switch Phase 0 — Django/DRF audit (item 68)

- **Date**: 2026-11-03
- **Work item**: 68
- **By**: OpenCode agent

## Summary

Phase 0 of the Laravel Backend Switch completed: full audit of the existing Django/DRF backend
before any Laravel coding. This is a docs-only, non-destructive change. Django backend is
untouched and stays active until Laravel cutover acceptance (master plan Rule 6/§25.8).

## Before

No Laravel plan or migration baseline existed in the repo. Backend knowledge lived only in code:
- `backend/` = Django + DRF, DB-per-tenant via active-connection switching
- 10 platform modules (saas central + 9 tenant modules)
- ~45 API endpoints with mixed role permissions
- Money domain with append-only enforcement (405s)
- Conflicts undocumented (money units vs DECIMAL, /api/v1 vs current paths)

## After

| Deliverable | File |
|---|---|
| Master plan (client doc saved + open-decisions table) | `docs/LARAVEL_SWITCH_MASTER_PLAN.md` |
| Phase 0 audit (all 8 deliverables of master plan §5) | `docs/LARAVEL_SWITCH_AUDIT.md` |
| Decisions appended (history intact) | `docs/DECISIONS.md` |

Audit contents: existing module list; API inventory (endpoint, method, auth, permission, side
effects, Laravel target TBD, status = Not started ×45); database schema inventory (3 central +
27 tenant tables with types/constraints/enums); authentication/permission matrix (session flows,
cross-host guard, role matrix read/create/update/delete per resource); external integrations
(none coded — MET Norway decision-only); file storage (none); 10 known issues/tech debt; 8
migration risks with mitigations (critical: DB-per-tenant runtime connection switching).

## Why

Master plan migration principle 1: audit before coding; do not infer functionality from folder
names. Client directed the rework on 2026-11-03.

## Verification

- Audit facts extracted by deep code exploration with file paths (config, tenancy, all modules).
- No tests executed (not App-eligible; doc-only item). No Docker/SSH/deploy actions.
- Kanban checks pending final run (next section).

## Files affected

- `docs/LARAVEL_SWITCH_AUDIT.md` (new)
- `docs/LARAVEL_SWITCH_MASTER_PLAN.md` (new)
- `docs/DECISIONS.md` (2 rows appended)
- `work/` trackers: item 68 todo→inprogress→completed, PLAN, SUMMARY, MEMORY
- this report

## Next work item (Stage A/B)

- 69 (proposed): Laravel foundation — verify PHP/composer, create `backend-laravel/`, PostgreSQL
  config — AFTER client confirms Stage A decisions (Laravel ✓ given; Next.js-fence = Vue/Nuxt
  stays ✓ default; module-gap decisions in §22 remain open).
