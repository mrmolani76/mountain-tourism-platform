# Master Dashboard

Refresh on every work / bug / change status update.

**Focus:** backend / API only until user opens frontend.

## 1. Work

### Todo
- **07** — Super admin APIs — complex + owner activate/deactivate
- **08** — VPS setup script/guide for API
- **09** — GitHub Actions — backend lint/test/deploy
- **10** — Foundation seed — §2 demo buildings (partial)

### In Progress
- **17** — Fix BUG-TENANT-001 — bind staff session to issuing tenant

### Completed
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
- **BUG-TENANT-001** — Cross-host session identity swap risk (medium, 2026-11-02)

### Verified
- (none)

### Closed
- (none)

## 3. Testing
- 2026-11-02 — item 06: `manage.py check` clean; migration-model check clean; new config tests code-only (suite runs on setup machine)
- 2026-11-02 — tests rewritten for DB-per-tenant / SaaS registry (not run — code-only item 11)
- 2026-11-02 — prior shared-DB isolation/auth results obsolete after tenancy change

## 4. Changes
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

## 5. Deploys
- (none yet)
