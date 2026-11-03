# Project Audit Report — Mountain Tourism Platform

- **Date**: 2026-11-03
- **By**: Agent
- **Scope**: Full repo after items **18–26** (DRF domain modules, SPA, Docker, tenant E2E login) + standing **code-first** rule
- **Work item**: 27
- **Prior audit**: `work/audits/2026-11-02-project-audit.md` (item 13, stubs-era)
- **Kanban**: `bash scripts/check-work-kanban.sh` → OK
- **Git**: `main` at `170262e` (code-first docs); working tree audited live

---

## 1. Executive verdict

| Area | Verdict |
|------|---------|
| Overall | **Scaffold + CRUD shell is large; MVP business depth is thin.** Not pilot-day ready. |
| Module A (core/saas) | **Strongest area** — auth, buildings/outlets/seasons/settings, SaaS register/activate; E2E staff login verified (item 26). |
| Modules B–I APIs | **Models + ViewSets exist**, but many flows are CRUD-only; hotel/folio/rental/… missing PDF “done when” logic. |
| Money (Rule 5) | **Direction correct** (void endpoints, 405 on update/delete). Gaps: Decimal vs integer cents; client-supplied `voided_by`; broken model `void()` helper. |
| Tenancy | **DB-per-tenant direction correct**; isolation test code present; runtime E2E login on one tenant verified. Full automated suite not re-run this audit. |
| AuthZ on domain APIs | **Critical defect** — hotel/folio/pos/rental/services/transport/weather/reports ViewSets set `authentication_classes = []` while requiring `IsAuthenticated` → session never binds → endpoints unusable (always 403) or authz bypassed if permissions weaken. Core/saas mostly correct. |
| Frontend | **Shell only** — EQMS template (customers/invoices/products) + 8 module placeholders. Not mountain desk workflows. |
| Tests | **~38 test defs**, all under core/saas/auth/isolation. **Zero** folio/hotel/pos/money/rental tests. |
| Docs / kanban | Living docs rich; PLAN phases still say Phase 1 “active” while 06–10 completed and domain CRUD already started. MEMORY.md stale. |
| Ship readiness | **No** for pilot winter/summer day. |

**One-line:** Layout and Module A are real; B–I are API skeletons; frontend is placeholder; one high auth bug blocks domain APIs.

---

## 2. Architecture snapshot

```text
Host saas.*     → central DB → modules.saas (Tenant, Domain, SaasUser)
Host {tenant}.* → tenant DB  → modules.core + hotel, folio, pos, rental,
                               services, transport, weather, reports
                 tenancy/ middleware + connections + db_router
SPA :3000       → /api proxy → Django :8000/8010
```

| Layer | Path | Status |
|-------|------|--------|
| Platform | `backend/tenancy/` | Present |
| Central | `backend/modules/saas/` | Models + APIs + provision services |
| Tenant core | `backend/modules/core/` | Staff, Building, Outlet, Season, Settings, auth |
| Domain | hotel, folio, pos, rental, services, transport, weather, reports | Models + DRF CRUD; thin/no services on several |
| Config | `backend/config/` | settings, urls, OpenAPI schema endpoint |
| Common | `backend/common/` | pagination, envelope (item 16/18) |
| Tests | `backend/tests/` | api + integration isolation only |
| Frontend | `frontend/` | Nuxt SPA wired; domain pages = placeholders |
| Ops | Docker, VPS docs, CI | Present (setup done when asked); standing rule: no more setup unless asked |

### Size (approx.)

| Area | Measure |
|------|---------|
| Backend Python (excl. migrations) | ~5.5k lines |
| Frontend vue/ts/js | ~22k lines (mostly template/@core) |
| Domain models | 28 model modules |
| Migrations | 14 initial/versioned files |
| Test files with `test_` | 4 files / ~38 methods |

---

## 3. Module maturity vs MVP

Legend: **Done** ≈ PDF “done when”; **Partial** = models/CRUD without acceptance flows; **Shell** = placeholder/stub; **Gap** = missing entity or rule.

| Module | Code | PDF acceptance (short) | Audit status | Main gaps |
|--------|------|------------------------|--------------|-----------|
| A Core / SaaS | Strong | Two complexes cannot see each other’s data (automated) | **Partial → near** | Isolation tests exist; suite not re-verified this audit. Seed buildings partial. |
| B Hotel | Models + ViewSets | 3-night HB posts 3 room + 3 board; checkout zero | **Partial** | No Reservation, HutBed, RoomSeasonPrice, nightly posting. Check-in does **not** open Folio. |
| C Folio | Models + services + void | Append-only money; checkout gate | **Partial** | Void/create path OK in services. Amounts = Decimal not integer cents. No charge/payment audit trail module wired. Domain auth broken (see §5). |
| D POS | Models + thin services | Charge to room; board-included not re-sold | **Partial** | Order can link folio FK; no post-to-folio charge service; no board inclusion check. |
| E Rental | Model + ModelViewSet | No double-book; damage fee → folio | **Shell/Partial** | Single Rental row; no inventory/SKU pool; no conflict service; empty `services/`. |
| F Services | Service + Slot CRUD | Capacity / slot conflict | **Shell/Partial** | No booking headcount logic; empty `services/`. |
| G Transport / lifts | Trip, Vehicle, Driver | Shuttle/taxi/pickup; lift status | **Shell/Partial** | **No Lift / LiftPass models.** Transport only. |
| H Weather | Limit + Warning CRUD | Limits → warnings on affected ops | **Shell/Partial** | No propagation to cancel/void with reason `weather`. |
| I Reports | Report model CRUD | Owner dashboard aggregates | **Shell** | No real aggregates; CRUD on Report metadata only. |
| Frontend desks | Placeholders | Role-aware desk UIs | **Shell** | `pages/modules/*.vue` = 9-line placeholders; leftover EQMS customers/invoices/products. |

### Phase plan vs reality

| Phase | PLAN status | Reality |
|-------|-------------|---------|
| 1 Foundation | Active (todos 02–10) | Kanban 02–10 **completed**; Module A largely coded |
| 2 Hotel + folio | Planned | Models/CRUD landed early (items 19–20); acceptance flows **not** done |
| 3 POS / rental / services | Planned | CRUD landed (21–22); business rules missing |
| 4 Transport / lifts / weather | Planned | Partial transport + weather CRUD; lifts missing |
| 5 Dashboard / handover | Planned | Report stub only |

---

## 4. Non-negotiable rules check

| Rule | Result | Notes |
|------|--------|-------|
| 1 Scope | Partial | Domain vocabulary mostly used; EQMS leftover names on frontend |
| 2 Git approval | OK | Process followed in completed items |
| 3 Tenant isolation | Direction OK | Host → Domain → tenant DB; no `tenant_id` on tenant tables |
| 4 No drive-by deps | OK this audit | Not scanning lockfile churn |
| 5 Money append-only | Partial | ViewSet blocks update/delete; void actions exist; model `void()` buggy; client `voided_by` |
| 6 Domain names | Mostly OK | Backend names align; frontend still Customer/Invoice/Product |
| 7 AuthZ on server | **Fail on B–I ViewSets** | `authentication_classes = []` clears default `StaffSessionAuthentication` |
| 8 Migrations immutable | OK | Append-only migration files present |
| 9 Change reports | OK | Present for completed work |
| 10 Work kanban | OK | Script passes; MEMORY stale |
| 11 Bug reports | OK | Open bugs: none |
| 12 Tests for money/tenancy | **Weak** | Tenancy tests exist; **no** folio/void/checkout/rental conflict tests |
| 13 Secrets | OK | No `.env` committed observed; E2E password not stored in repo |

---

## 5. Findings (severity)

### Critical

1. **Domain ViewSets disable authentication**  
   Files: `backend/modules/{hotel,folio,pos,rental,services,transport,weather,reports}/views/*.py`  
   Pattern: `authentication_classes = []` + `permission_classes = [IsAuthenticated, …]`  
   Effect: Staff session never authenticates → requests fail authz (403). Core/saas ViewSets correctly use `StaffSessionAuthentication`.  
   Fix direction: remove empty list or set `[StaffSessionAuthentication]` (separate coding + bug item).

### High

2. **Hotel check-in does not create Folio**  
   `check_in_stay` only flips status. PDF/Module B: check-in → Stay + Folio.

3. **MVP entities missing**  
   Reservation, HutBed / bed inventory, RoomSeasonPrice, NightlyPostingJob, Lift, LiftPass, real Report aggregates.

4. **Almost no domain tests**  
   Rule 12 unmet for folio posting, voids, board meals, rental double-book, capacity, checkout zero-balance.

### Medium

5. **Money as Decimal, not integer minor units**  
   Phase 2 / module docs require integer cents; models use `DecimalField`.

6. **`voided_by` taken from request body**  
   Should bind to `request.user` on server (Rule 7).

7. **Charge/Payment model `.void()` assigns `models.DateTimeField.auto_now`**  
   Dead/broken helper; service layer uses `timezone.now()` correctly. Remove or fix model methods.

8. **POS / rental / services empty business services**  
   Thin or absent `services/` → conflict/capacity/charge-to-room rules not enforced.

9. **Frontend domain mismatch**  
   Template CRUD (users/customers/invoices/products) vs mountain modules placeholders.

10. **PLAN / MEMORY drift**  
    Phases still “Active — Module A todos 02–10”; MEMORY still suggests pickup 06/07.

### Low

11. **OpenAPI** = DRF core schema only (no spectacular). Acceptable for MVP if documented.

12. **`docs/BACKEND_STRUCTURE_GAPS.md`** still describes domain as stubs in places (stale vs items 19–22).

13. **AGENTS.md “Key Project Facts”** still says shared-DB `tenant_id` in one bullet while CLAUDE is DB-per-tenant (docs twin drift).

---

## 6. What works (strengths)

- Clear `modules/` layout matching Complere/EQMS intent
- DB-per-tenant middleware + router + SaaS registry
- Staff session bound to issuing tenant (BUG-TENANT-001 fixed)
- Core config APIs: buildings, outlets, seasons, settings, staff, outlet access
- Folio service layer: open/close, post charge, record payment, void (append-only intent)
- Docker + tenant host E2E login verified (items 24–26)
- Rich module docs under `docs/modules/`
- Work tracker / change reports / bug INDEX discipline

---

## 7. Verification this audit

| Check | Result |
|-------|--------|
| Kanban script | OK |
| Inventory of models / urls / tests | Done (filesystem) |
| Code review of folio/hotel/auth patterns | Done |
| `manage.py test` / full suite | **Not run** (audit/docs item; code-first; no explicit verify ask) |
| Browser / E2E re-login | Not re-run; trust item 26 report |
| Security exploit PoC | Not written (review only) |

---

## 8. Recommended next actions (product/API code first)

Ordered for MVP acceptance, not for more setup:

| # | Action | Type |
|---|--------|------|
| 1 | Fix `authentication_classes` on all domain ViewSets; add bug report | Bug + coding |
| 2 | Folio/hotel acceptance path: check-in → folio; nightly posting; integer money; void actor = user | Coding |
| 3 | Tests: tenant isolation green + folio void/checkout + board posting | Testing agent |
| 4 | Reservation + hut beds + season prices | Coding (Module B) |
| 5 | POS charge-to-folio + board inclusion guard | Coding (D↔B/C) |
| 6 | Rental conflict + damage → charge; slot capacity | Coding (E/F) |
| 7 | Lift models + weather void reason wiring | Coding (G/H) |
| 8 | Replace frontend placeholders with desk flows **only when user opens frontend focus** | Frontend |
| 9 | Refresh PLAN phase status + MEMORY + BACKEND_STRUCTURE_GAPS | Docs |

---

## 9. Delta since 2026-11-02 audit

| Then | Now |
|------|-----|
| Domain modules stubs | Models + migrations + ViewSets for B–I |
| No folio money | Folio/Charge/Payment + void services |
| No hotel models | Room/Stay/BoardPlan + thin check-in |
| Frontend deferred / absent in repo | SPA present, wired, login works; desks placeholder |
| Setup not done | Docker + tenant E2E done when asked |
| Auth pattern only on core | Domain modules incorrectly cleared auth classes |
| Next: setup + 06/07 | Next: fix domain auth + deepen B/C acceptance |

---

## 10. Audit deliverables

- This file: `work/audits/2026-11-03-project-audit.md`
- Work item 27 completed with change report
- Interactive summary canvas (IDE): project-audit-2026-11-03

No application code was modified in this audit item.
