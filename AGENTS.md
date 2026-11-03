# AI Agent Instructions

## Primary Rule Source
**Follow `CLAUDE.md`** — single source of truth for architecture, domain, and non-negotiables.

Engineering standards (TDD / tests / security-first / completion): `.cursor/rules/nexro-engineering.mdc` + `.opencode/rules.md` (keep twins in sync).

## Response Style

| Rule | Meaning |
|---|---|
| No over-explain | Get to the point. |
| Simple words | Easy words. |
| Hinglish in chat | Hindi + English mix in chat. |
| English in the repo | All repo files stay English. |
| No long paragraphs | Short pieces. |
| Points and tables | Bullets or tables. |
| Crisp | Only what is needed. |
| No em-dashes | Use comma or period. |

## Required Skills

Configured under `.cursor/skills/` (mirrors: `.opencode/skills/`, `.claude/skills/`).

| Skill | Trigger |
|-------|---------|
| `django-best-practices` | Django / DRF / `backend/**/*.py` |
| `nuxt-vuetify-best-practices` | Nuxt / Vue / Vuetify / `frontend/**` |
| `coding-agent` | implement, build, fix bug, refactor |
| `testing-agent` | write tests, test plan (do not run by default) |
| `devops-agent` | Docker, Compose, CI, deploy scripts |
| `security-code-review` | security / OWASP review (review only) |
| `work-tracker` | start / pick up / complete work items |
| `change-report` | after any change |
| `bug-tracker` | confirmed bug |
| `triage-before-fix` | red / flaky tests |
| `verify-before-done` | before "done" / "finished" |

## Quick Start for Agents
1. Read `CLAUDE.md`
2. Check `.cursor/rules/scope.mdc` (alwaysApply → CLAUDE.md)
3. Use domain vocabulary from CLAUDE.md (Tenant, Building, Outlet, Folio, Charge, …)
4. Money is append-only (void only)
5. Tenant scope every business query
6. Before edits on a tracked item: `work-tracker` (MD `todo` → `inprogress` + PLAN/SUMMARY). Detail: `work/README.md`
7. WIP limit = 1. Validate: `bash scripts/check-work-kanban.sh`
8. Before saying done: `verify-before-done` + Completion Log + change report + MD → `completed`
9. Commit/push only after explicit user approval ("Commit aur push karun?")

## Key Project Facts
- **Product**: Mountain tourism complex SaaS (MVP scope PDF in `document/`)
- **Backend**: Django + DRF in `backend/`
- **Frontend**: Nuxt + Vue + Vuetify in `frontend/`
- **Tenancy**: `tenant_id` on every business table (shared DB MVP)
- **Hard rule**: Charge/Payment never edit/delete — void with reason + user + timestamp
- **No setup by default**: do not start Docker/Compose/VPS/CI/env/seed/infra work unless the user asks; write product/API code
- **Git**: ask "Commit aur push karun?" — never commit alone
