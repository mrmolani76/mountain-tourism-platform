# Master Dashboard

Refresh on every work / bug / change status update.

**Focus:** product/API **code** only. No Docker/VPS/CI/env/setup unless user asks. Backend first; frontend deferred.

## 1. Work

### Todo
- (none)

### In Progress
- (none)

### Completed
- **27** — Project audit complete report (post items 18–26) (high, 2026-11-03)
- **26** — Tenant dev setup — physical DB, migrate, owner seed, E2E login verify (high, 2026-11-03)
- **25** — Fix BUG-APP-001 — prod SPA blank screen (register Nuxt modules) (high, 2026-11-03)
- **24** — Docker dev setup — inventory-style dev stack (compose, docs, scripts, verify) (high, 2026-11-03)
- **23** — Frontend SPA — copy into repo and wire to Django backend (high, 2026-11-03)
- **22** — remaining modules — rental, services, transport, weather, reports DRF APIs (2026-11-02)
- **21** — pos module — Product, Order, OrderLine models + DRF APIs (2026-11-02)
- **20** — hotel module — Room, Stay, BoardPlan models + DRF APIs (2026-11-02)
- **19** — folio module — Folio, Charge, Payment models + DRF APIs (2026-11-02)
- **18** — DRF settings — pagination, filtering, JSON envelope (2026-11-02)
- **10** — Foundation seed — §2 demo buildings (partial) (2026-11-02)
- **09** — GitHub Actions — backend lint/test/deploy (2026-11-02)
- **08** — VPS setup script/guide for API (2026-11-02)
- **07** — Super admin APIs — complex + owner activate/deactivate (2026-11-02)
- **17** — Fix BUG-TENANT-001 — bind staff session to issuing tenant (2026-11-02)
- **16** — Backend structure gaps fill (pyproject, common, tests split, admin, env stub) (2026-11-02)
- **06** — Buildings, outlets, seasons, tenant settings APIs (2026-11-02)
- **15** — Backend structure gaps MD (2026-11-02)
- **14** — Audit module + business logic docs (2026-11-02)
- **13** — Project audit report (2026-11-02)
- **12** — EQMS-style modules layout (2026-11-02)
- **11** — DB-per-tenant backend layout (DRF) (2026-11-02)
- **01** — Harden work-tracker kanban workflow (2026-11-02)
- **02** — Architecture note + core data model draft (2026-11-02)
- **03** — Django + DRF backend scaffold (2026-11-02)
- **04** — Tenant isolation layer + cross-tenant test (2026-11-02)
- **05** — Auth API — login, roles, outlet access, password reset (2026-11-02)

## 2. Bugs

### Open
- (none)

### Verified
- **BUG-APP-001** — Prod SPA blank screen — Nuxt modules not registered (high, 2026-11-03)
- **BUG-TENANT-001** — Cross-host session identity swap risk (medium, 2026-11-02)
- **BUG-ENV-001** — Tenant DB template clone missing OPTIONS key (high, 2026-11-02)

### Closed
- (none)

## 3. Testing
- 2026-11-02 — item 06: `manage.py check` clean; migration-model check clean; new config tests code-only (suite runs on setup machine)
- 2026-11-02 — tests rewritten for DB-per-tenant / SaaS registry (not run — code-only item 11)
- 2026-11-02 — prior shared-DB isolation/auth results obsolete after tenancy change

## 4. Changes
- 2026-11-03 — `work/changes/2026-11-03--27-project-audit-complete-report.md` — full project audit (item 27); report at `work/audits/2026-11-03-project-audit.md`
- 2026-11-03 — `work/changes/2026-11-03--code-first-no-setup-rule.md` — standing rule: product/API code only; no Docker/VPS/CI/env setup unless user asks
- 2026-11-03 — `work/changes/2026-11-03--26-tenant-dev-setup-e2e-login.md` — tenant dev setup (item 26): lab_local DB, migrated, owner seeded, E2E staff login verified via SPA proxy
- 2026-11-03 — `work/changes/2026-11-03--25-spa-nuxt-modules-blank-screen.md` — BUG-APP-001: prod SPA blank screen fix (Nuxt modules + i18n wired); login page verified
- 2026-11-03 — `work/changes/2026-11-03--24-docker-dev-setup.md` — Docker dev setup (item 24): compose/nginx/Dockerfile fixes, docs, scripts, stack verified
- 2026-11-02 — `work/changes/2026-11-02--07-to-10-super-admin-vps-ci-seed.md` — items 07-10 (super admin, VPS, CI, seed)
- 2026-11-02 — `work/changes/2026-11-02--16-backend-structure-gaps-fill.md` — gaps fill (item 16)
- 2026-11-02 — `work/changes/2026-11-02--06-core-config-apis.md` — core config APIs (item 06) + BUG-TENANT-001
- 2026-11-02 — `work/changes/2026-11-02--15-backend-structure-gaps-md.md` — BACKEND_STRUCTURE_GAPS.md
- 2026-11-02 — `work/changes/2026-11-02--14-audit-module-business-logic-docs.md` — AUDIT_MODULE.md + services/ BL
- 2026-11-02 — `work/changes/2026-11-02--13-project-audit-report.md` — audit + DEPENDENCIES.md (no setup)
- 2026-11-02 — `work/changes/2026-11-02--12-eqms-style-modules-layout.md` — Complere-style `modules/` layout
- 2026-11-02 — `work/changes/2026-11-02--11-db-per-tenant-backend-layout.md` — DB-per-tenant + DRF layout
- 2026-11-02 — `work/changes/2026-11-02--02-m1-architecture-data-model.md` — Module A architecture note + DECISIONS rows
- 2026-11-02 — `work/changes/2026-11-02--03-m1-django-drf-scaffold.md` — backend/ Django + DRF scaffold
- 2026-11-02 — `work/changes/2026-11-02--04-m1-tenant-isolation.md` — tenant isolation layer + cross-tenant test
- 2026-11-02 — `work/changes/2026-11-02--05-m1-auth-roles-api.md` — Auth API: login, roles, outlet access, password reset
- 2026-11-02 — `work/changes/2026-11-02--05-github-actions-ci-testing.md` — CI testing workflow
- 2026-11-02 — `work/changes/2026-11-02--04-phase1-module-a-todo-list-design.md` — Module A todo list design
- 2026-11-02 — `work/changes/2026-11-02--03-phase-complete-module-descriptions.md` — phase embeds full modules A–I
- 2026-11-02 — `work/changes/2026-11-02--02-phase-work-plans-backend-focus.md` — phase plans + P1 todos (API-first)
- 2026-11-02 — `work/changes/2026-11-02--01-work-tracker-workflow.md` — work-tracker workflow
- 2026-11-02 — `work/changes/2026-11-02--ai-rules-and-skills-bootstrap.md` — AI rules/skills bootstrap
- 2026-11-02 — `work/changes/2026-11-02--22-remaining-modules-drff-apis.md` — remaining modules: rental, services, transport, weather, reports DRF APIs (item 22)
- 2026-11-02 — `work/changes/2026-11-02--21-pos-module-models-drff-apis.md` — pos module: Product, Order, OrderLine models + DRF APIs (item 21)
- 2026-11-02 — `work/changes/2026-11-02--20-hotel-module-models-drff-apis.md` — hotel module: Room, Stay, BoardPlan models + DRF APIs (item 20)
- 2026-11-02 — `work/changes/2026-11-02--19-folio-module-models-drff-apis.md` — folio module: Folio, Charge, Payment models + DRF APIs (item 19)
- 2026-11-02 — `work/changes/2026-11-02--18-drf-settings-pagination-filtering-envelope.md` — DRF settings: pagination, filtering, JSON envelope (item 18)

## 5. Deploys
- (none yet)
