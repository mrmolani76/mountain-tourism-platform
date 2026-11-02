# OpenCode Project Rules

> **Source of truth**: repo-root `CLAUDE.md` + `AGENTS.md`. If anything conflicts, follow `CLAUDE.md`.

## Project Overview
**Mountain Tourism Complex Platform** — multi-tenant SaaS for mountain complexes (hotel, F&B, rental, spa, lifts, transport, weather). One guest folio across outlets.

### Stack
- **Backend**: Django + Django REST Framework (`backend/`)
- **Frontend**: Nuxt + Vue.js + Vuetify (`frontend/`)
- **Tenancy**: shared DB, `tenant_id` on every business table (MVP)
- **Money**: Charge/Payment append-only; void with reason + user + timestamp
- **Scope contract**: `document/Mountain_MVP_Scope_v1.pdf`

### Domain names (do not rename casually)
Tenant, Building, Outlet, Season, Stay, Board plan, Folio, Charge, Payment, Service, Slot, Rental, Trip, Lift, Weather limit, Warning.

---

## Engineering standards (twin of `.cursor/rules/nexro-engineering.mdc`)

1. TDD where practical; regression tests for bugs
2. Unit tests for business logic and edge cases
3. Integration tests for API, authz, workflows
4. Security first: server authz + tenant isolation
5. Adversarial review after features (report-only unless authorized to fix)
6. Watch N+1 / expensive queries
7. Follow repo conventions; do not weaken tests to pass CI
8. Never claim done without verification

---

## OpenCode skills (keep in sync with `.cursor/skills/`)

Skills: `.opencode/skills/<name>/SKILL.md`. Cursor mirrors: `.cursor/skills/`. Claude: `.claude/skills/`.

| Skill | When |
|-------|------|
| `coding-agent` | Implement / fix / refactor — code-only default |
| `devops-agent` | Docker/CI/deploy config — config-only default |
| `testing-agent` | Author tests — do not run by default |
| `security-code-review` | Review only |
| `django-best-practices` | Backend Python/Django |
| `nuxt-vuetify-best-practices` | Frontend Nuxt/Vue/Vuetify |
| `work-tracker` | Kanban under `work/` |
| `change-report` | After any change |
| `bug-tracker` | Confirmed bugs |
| `triage-before-fix` | Red/flaky tests |
| `verify-before-done` | Before claiming done |

## Response style
Chat = Hinglish. Repo files = English. Short, tables/bullets, no em-dash.

## Git
Ask **"Commit aur push karun?"** — never commit/push without explicit user approval.
