# OpenCode Project Rules

> **Source of truth**: repo-root `CLAUDE.md` + `AGENTS.md`. If anything conflicts, follow `CLAUDE.md`.

## Project Overview
**Mountain Tourism Complex Platform** — multi-tenant SaaS for mountain complexes (hotel, F&B, rental, spa, lifts, transport, weather). One guest folio across outlets.

### Stack
- **Backend**: Laravel + nwidart Modules (`backend-laravel/`)
- **Frontend**: Nuxt + Vue.js + Vuetify (`frontend/`)
- **Tenancy**: DB-per-tenant; Host/`Domain` resolve; central Saas registry
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
9. Triage CI failures before code/test changes; never bypass required checks
10. Use immutable migrations: add a new migration for a schema change
11. Run browser E2E only in an authorized test environment with existing credentials
12. No auto-commit, auto-push, PR creation, or merge; get the user's explicit approval

---

## OpenCode skills (keep in sync with `.cursor/skills/`)

Skills: `.opencode/skills/<name>/SKILL.md`. Cursor mirrors: `.cursor/skills/`. Claude: `.claude/skills/`.

| Skill | When |
|-------|------|
| `coding-agent` | Implement / fix / refactor — code-only default |
| `devops-agent` | Docker/CI/deploy config — config-only default |
| `testing-agent` | Author tests — do not run by default |
| `security-code-review` | Review only |
| `laravel-best-practices` | Backend Laravel / `backend-laravel/**` |
| `nuxt-vuetify-best-practices` | Frontend Nuxt/Vue/Vuetify |
| `work-tracker` | Kanban under `work/` — folder=status; pickup before edits; PLAN+SUMMARY sync; `scripts/check-work-kanban.sh` |
| `change-report` | After any change |
| `bug-tracker` | Confirmed bugs |
| `triage-before-fix` | Red/flaky tests |
| `verify-before-done` | Before claiming done |
| `ci-failure-triage` | Failed GitHub Actions runs, classify before edits |
| `ci-failure-report` | Requested CI report-only investigation |
| `browser-e2e` | Requested browser acceptance testing |
| `dev-test-fix-loop` | Explicit full development/test/fix lifecycle |
| `bug-fix-workflow` | Explicit bug lifecycle, no auto-ship |
| `github-pr-workflow` | Explicit GitHub issue/branch/PR requests |
| `supervisor-agent` | Requested skill routing/orchestration |
| `infer-conventions` | Requested convention audit |
| `testing-best-practices` | Test design and review |
| `tailwindcss-development` | Tailwind UI work, follow installed version |
| `auth-development` | Laravel Sanctum auth and authorization |
| `task-interruption-safety` | Preserve state when pausing/resuming work |

Cursor-specific rule files live in `.cursor/rules/`; all clients follow root `CLAUDE.md` on conflicts. Skills added from Nexro are adapted to the local Laravel + Nuxt stack; no Nexro credentials, private production details, or automatic Git/deployment permissions are imported.

## Response style
Chat = Hinglish. Repo files = English. Short, tables/bullets, no em-dash.

## Git
Ask **"Commit aur push karun?"** — never commit/push without explicit user approval.
