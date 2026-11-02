# Project Audit Report — Mountain Tourism Platform

- **Date**: 2026-11-02
- **By**: Agent
- **Scope**: Backend architecture after items **11** (DB-per-tenant) + **12** (EQMS-style modules). Frontend deferred. SSO out of scope.
- **Work item**: 13
- **Kanban**: `bash scripts/check-work-kanban.sh` → OK at audit start
- **Git**: `main` ahead of `origin/main` by 1 (`b173533` DB-per-tenant + modules refactor). Working tree clean at audit start.

---

## 1. Executive verdict

| Area | Verdict |
|------|---------|
| Overall | **Layout aligned with EQMS/Complere intent** (modules + DB-per-tenant + DRF). Not production-ready: migrations/tests not applied/run; domain modules B–I are stubs; money/folio not built. |
| Tenancy | **Correct direction** (DB-per-tenant). Physical provision/setup intentionally not done. |
| Module layout | **Pass** — `backend/modules/` mirrors Complere `Modules/{Name}`. |
| DRF / DTO | **Pass for MVP** — serializers = DTO boundary (no separate dto package; no SSO). |
| Docs consistency | **Partial** — living architecture updated; some module index still said shared `tenant_id` / `apps/`. Fixed in this audit. |
| Tests | **Code present, not verified** (user code-only policy on 11/12). Prior shared-DB green results are obsolete. |

**Ship readiness for pilot day:** No — foundation layout only. Next: apply migrations, make multi-DB tests green, implement Module A APIs (06/07), then B/C money path.

---

## 2. Architecture snapshot

```text
Host saas.*  →  central DB  →  modules.saas (Tenant, Domain, SaasUser)
Host {slug}.* → tenant DB   →  modules.core (+ hotel…reports stubs)
                 tenancy/ middleware + connections + db_router
```

| Layer | Path | Status |
|-------|------|--------|
| Platform | `backend/tenancy/` | Present (middleware, context, connections, provision stubs) |
| Central module | `backend/modules/saas/` | Models + DRF ViewSets + services |
| Tenant foundation | `backend/modules/core/` | Staff, Outlet stub, auth APIViews/ViewSets |
| Domain stubs | hotel, folio, pos, rental, services, transport, weather, reports | Empty packages + `module.json` |
| Config | `backend/config/` | settings, urls, `db_router.py` |
| Tests | `backend/tests/` | auth, saas registry, tenant DB isolation (not run this audit) |

### EQMS → Mountain mapping

| Complere | Mountain | OK? |
|----------|----------|-----|
| `Modules/{Name}/` | `modules/{alias}/` | Yes |
| `App/Models` | `models/` | Yes |
| `App/Services` | `services/` | Yes |
| `App/Data` (DTO) | `serializers/` (DRF) | Yes (by design) |
| `App/Http` | `views/` | Yes |
| `App/Policies` | `permissions/` | Yes |
| `routes/api.php` | `urls.py` | Yes |
| `Database/migrations` | `migrations/` | Yes |
| `module.json` | `module.json` | Yes |
| `app/` + stancl | `tenancy/` | Yes (custom, no new package) |
| SSO / SAML | — | Explicitly excluded |

---

## 3. Module inventory

| Alias | central | Implemented surface | Gap |
|-------|---------|---------------------|-----|
| saas | yes | Tenant, Domain, SaasUser; login/me; TenantViewSet register/activate/deactivate | No physical createdb/migrate/seed owner |
| core | no | Staff, StaffOutletAccess, Outlet stub; auth + Staff ViewSets | Buildings, seasons, tenant settings APIs (todo 06) |
| hotel | no | Stub only | Phase 2 |
| folio | no | Stub only | Phase 2 — money append-only critical |
| pos | no | Stub only | Phase 3 |
| rental | no | Stub only | Phase 3 |
| services | no | Stub only | Phase 3 |
| transport | no | Stub only | Phase 4 |
| weather | no | Stub only | Phase 4–5 |
| reports | no | Stub only | Phase 5 |

---

## 4. Tenancy & security audit (code review, no exploit PoC)

| Check | Result | Notes |
|-------|--------|-------|
| Isolation mechanism | DB connection via Host → Domain | `TenantResolveMiddleware` + `TenantDatabaseRouter` |
| Row `tenant_id` on business tables | Removed from Staff/Outlet | Correct for DB-per-tenant |
| Cross-tenant API | Not possible via same DB filter; wrong host 404 | Depends on Domain rows + connection activation |
| Client-supplied tenant id | Not used for authz | Good |
| SaaS vs tenant host split | `/api/saas/` vs `/api/` | Present |
| SSO | Absent | Per decision |
| Secrets in git | Not audited deep; `.env` pattern expected | Keep Rule 13 |
| Money append-only | N/A yet | Folio not implemented — enforce when built |
| Auth | Session Staff / SaasUser | Email+password only |

**Risks (severity):**

1. **High (ops)** — Migrations written (`saas.0001`, `core.0004`) but not applied in this stream; local/CI will break until multi-DB test harness exists.
2. **High (correctness)** — Tests assume tenant aliases; `databases = {'default'}` only in TestCase — multi-DB test config incomplete until setup work.
3. **Medium** — `allow_migrate` for tenant apps skips `default` unless hint; easy to confuse operators running plain `migrate`.
4. **Medium** — DECISIONS table still lists older “shared DB / scoped manager” as Accepted without Superseded label (historical rows kept; clarified this audit).
5. **Low** — Stub modules have empty `urls.py`; fine until wired in `config/urls.py`.

---

## 5. DRF / DTO

- Boundary validation = **DRF serializers** under `modules/*/serializers/`.
- No separate `dto/` package (Complere `App/Data` equivalent by role, not by name).
- Resource APIs = ViewSets + `DefaultRouter`; auth = `APIView`.
- Thin views → `services/` (saas + core auth services present).

---

## 6. Work kanban audit

| ID | Status | Note |
|----|--------|------|
| 01–05, 11, 12 | completed | Folder/PLAN/SUMMARY aligned (kanban script OK) |
| 06–10 | todo | Need re-read after tenancy change (07 = provision-oriented) |
| 04 | completed historically | Shared-DB isolation — **superseded by 11**; keep as history |
| Bugs | none open | — |

---

## 7. Documentation audit

| Doc | Status |
|-----|--------|
| `docs/ARCHITECTURE.md` | Updated for DB-per-tenant + modules |
| `CLAUDE.md` Rule 3 / Tenancy | Updated |
| `docs/DECISIONS.md` | Append-only; older tenancy rows need Superseded markers → fixed this audit |
| `docs/modules/README.md` | Still described `apps/` + shared `tenant_id` → fixed this audit |
| `backend/modules/README.md` | Good module map |
| Historical `work/completed/*` / old change reports | May say `apps.*` — leave as history |

---

## 8. Verification status

| Check | Result |
|-------|--------|
| Python syntax (`modules/**/*.py`) | OK (ast parse) |
| `manage.py test` / migrate | **Not run** (code-only policy on 11/12; not reopened this audit) |
| Kanban script | OK |
| Leftover `apps.core` imports in backend | None |
| `backend/apps/` directory | Absent |

---

## 9. Recommended next actions (ordered)

1. **Setup work** (todo 08 / new): local hosts, central + 2 tenant DBs, migrate saas + tenant apps, document runbook.
2. **Make tests green** under multi-DB settings; replace obsolete SUMMARY testing lines with real results.
3. **Todo 06** — buildings, outlets, seasons, settings on `modules.core`.
4. **Todo 07** — wire SaaS create to registry + owner seed (still no accidental createdb if policy says so; or open setup explicitly).
5. Phase 2 — `modules.hotel` + `modules.folio` (money append-only + void).
6. Do not add SSO unless product asks.

---

## 10. Audit remediation done in item 13

- Marked superseded tenancy decisions in `docs/DECISIONS.md`.
- Rewrote `docs/modules/README.md` cross-cutting + layout for `modules/` + DB-per-tenant.
- This audit file + change report.
