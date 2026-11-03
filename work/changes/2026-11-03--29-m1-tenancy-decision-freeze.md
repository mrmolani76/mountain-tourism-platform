# Change Report: Module 1 — tenancy decision freeze (item 29)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 29

## 1. Summary
Froze the MVP tenancy story in writing: DB-per-tenant + Host/`Domain` routing. No code change.

## 2. Before
- `docs/DECISIONS.md` had Accepted rows for DB-per-tenant + connection isolation (2026-11-02), but Module_1 spec §3.2 / §19 Q1–Q2 still listed the tenancy architecture as an open question awaiting client approval.

## 3. After
- `docs/DECISIONS.md`: appended **2026-11-03 Module 1 tenancy freeze** row (DB-per-tenant + Host/Domain = MVP isolation; supersedes PDF shared `tenant_id`; no purchased subdomain; isolation via active connection).
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` §3.2: added **RESOLVED 2026-11-03 (item 29)** note (full resolution text, keeps original wording).
- §19 Q1 and Q2: marked **RESOLVED (2026-11-03, MVP freeze, item 29)** with pointers to DECISIONS.
- `docs/ARCHITECTURE.md`: already describes DB-per-tenant (`{slug}_{env}`, no tenant_id) — no change needed.
- No architecture rewrite; questions Q3–Q19 remain open for client/owner.

## 4. Reason
Kanban 29: Module 1 closeout — freeze tenancy decision; unblocks honest Module 1 exit (item 33).

## 5. Files Affected
- `docs/DECISIONS.md`
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`

## 6. Verification / Status
- [x] DECISIONS has the frozen MVP row (append-only, no history rewrite)
- [x] Module_1 Q1–Q2 marked resolved with pointers
- [x] No app code changed
- [x] Kanban: item 29 moved to completed; PLAN + SUMMARY updated

## 7. Usage
Reference: `docs/DECISIONS.md` (2026-11-03 freeze row) and Module_1 §3.2.
