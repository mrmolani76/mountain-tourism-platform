# Change Report: Module 1 MVP policy defaults (item 32)

- **Date**: 2026-11-03
- **By**: OpenCode agent
- **Change ID**: 32

## 1. Summary
Documented MVP coding defaults for Module_1 §19 Q3–Q18 so Module 1 can close without waiting on every client answer. True client-only questions marked deferred. Q1/Q2 (tenancy) already resolved (item 29); Q19 (isolation source of truth) marked resolved too.

## 2. Before
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` §19 listed ~19 open questions awaiting client approval, some blocking day-to-day coding decisions.
- Q1/Q2 marked resolved (item 29); Q3–Q18 unresolved; Q19 not yet resolved in spec.

## 3. After
- `docs/DECISIONS.md`: appended **2026-11-03 Module 1 MVP policy defaults** row (append-only) covering:
  - Q3: complex create needs name+slug mandatory (rest optional)
  - Q4: new complex active immediately (`is_active=True`), Super Admin can deactivate later
  - Q5: deactivated tenant → login/API blocked with 404 `Tenant inactive.`
  - Q6: deactivation temporary/reactivable, no data loss
  - Q7: staff fields = email/password/role/is_active
  - Q8: Managers may reset staff passwords (existing `CanResetStaffPassword`); Owner creates staff (existing `IsOwner`)
  - Q9: Owner accesses all outlets implicitly; others via explicit `StaffOutletAccess`; staff may hold multiple outlet accesses
  - Q12-Q13: building holds multiple outlets/types; types are fixed code enums (not tenant-configurable)
  - Q14: seasons may NOT overlap (cross-kind, enforced via BUG-APP-003 fix)
  - Q15: tax_rates = JSON list of percentages
  - Q16: new tenant defaults currency=`EUR`, units=`metric`, timezone=`UTC`
  - Q17: audit log viewable by Owner/Manager (UI deferred to Module C)
  - Q18: audit retention = indefinite (append-only, no purge)
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md` §19: Q19 marked **RESOLVED** (pointer to DECISIONS). Q1/Q2 already RESOLVED (item 29).

## 4. Reason
Module_1 exit (item 33) requires coding defaults so agents don't treat every §19 question as a blocker; client-only questions deferred explicitly.

## 5. Files Affected
- `docs/DECISIONS.md`
- `work/phases/Module_1_Core_Super_Admin_Deep_Analysis.md`
- `work/changes/2026-11-03--32-m1-policy-defaults.md`
- `work/completed/32-m1-policy-defaults.md`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`

## 6. Verification / Status
- [x] DECISIONS row present (append-only; no history rewrite)
- [x] Spec §19 Q19 marked resolved; Q1/Q2 already resolved
- [x] No app code changed (docs only)
- [x] Kanban: item 32 → completed; PLAN + SUMMARY + MEMORY updated

## 7. Usage
Reference `docs/DECISIONS.md` (2026-11-03 defaults row) when implementing remaining Module 1 / Module 2 code.
