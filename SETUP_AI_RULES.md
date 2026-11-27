# AI Rules Setup — Mountain Tourism Platform

## Where Rules Live

```
mountain-tourism-platform/
├── CLAUDE.md                 # Single source of truth
├── AGENTS.md                 # Quick pointer for agents
├── SETUP_AI_RULES.md         # This file
├── .cursor/
│   ├── rules/                # Cursor always-apply + agent rules (.mdc)
│   └── skills/               # Cursor skills
├── .opencode/
│   ├── rules.md              # OpenCode summary twin
│   └── skills/               # Mirror of .cursor/skills/
├── .claude/
│   └── skills/               # Mirror for Claude Code
└── work/                     # Kanban + changes + bugs
```

Nexro's compatible developer workflows have been adapted for this platform's Laravel + Nuxt stack. Do not copy Nexro's private machine/deployment details or auto-commit, auto-push, or auto-deploy exceptions. `CLAUDE.md` remains authoritative.

## How Agents Load Rules

1. **Cursor**: `.cursor/rules/scope.mdc` has `alwaysApply: true` and references `@CLAUDE.md`
2. **Claude Code**: reads `CLAUDE.md` + `.claude/skills/`
3. **OpenCode**: `.opencode/rules.md` + `.opencode/skills/` (follow CLAUDE.md on conflict)
4. **Codex / Freebuff / others**: start from `AGENTS.md` → `CLAUDE.md`
5. **Skills** activate from their `description` triggers

## How Humans Update Rules

### Project-wide conventions
Edit **`CLAUDE.md` only**. `scope.mdc` stays a pointer.

### Skill / workflow changes
Edit the skill under `.cursor/skills/<name>/`, then copy the same folder to:
- `.opencode/skills/<name>/`
- `.claude/skills/<name>/`

### Engineering standards twin
Keep `.cursor/rules/nexro-engineering.mdc` and `.opencode/rules.md` aligned.

### Agent rules and skill mirrors
The full skills index is in `AGENTS.md` and `CLAUDE.md`. Add or update a skill in `.cursor/skills/<name>/SKILL.md`, then mirror the same file to `.claude/skills/<name>/` and `.opencode/skills/<name>/`.

Cursor-specific activation rules live in `.cursor/rules/`. The project-wide `scope.mdc` remains a pointer to `CLAUDE.md`; do not put conflicting architecture or approval rules in a client-specific file.

Nexro-specific skills that depend on absent or unrelated tooling are intentionally not enabled: Laravel Nightwatch and Laravel Cloud workflows. Nexro's Fortify guidance is adapted as `auth-development` for this repository's Sanctum setup. Browser E2E, CI reporting, bug-fix, PR, Tailwind, and interruption workflows are rewritten to match this repository and its explicit authorization gates.

| Nexro source | Mountain Tourism adaptation |
|---|---|
| `browser-client-e2e` | `browser-e2e`, covers the Nuxt staff UI and Laravel API, not ERP shop flows |
| `bug-fix-ship` | `bug-fix-workflow`, no automatic commit, push, or PR |
| `ci-failure-cloud-report` | `ci-failure-report`, local report-only, no automatic commit |
| `fortify-development` | `auth-development`, uses the installed Sanctum setup and forbids unapproved auth dependencies |
| `pr-workflow` | `github-pr-workflow`, only on explicit request and approval |
| `battery-safety` | `task-interruption-safety`, no guessed battery state or automatic Git writes |
| Tailwind guidance | `tailwindcss-development`, follows this frontend's installed Tailwind v3 |
| `configure-nightwatch` | Not enabled, Nightwatch is not an installed project dependency |
| `deploying-to-cloud` | Not enabled, this project does not use Laravel Cloud; use `devops-agent` for requested infrastructure work |
| `notify-update-email` | Not enabled, Nexro's `notify:update` command is not part of this project |

Nexro has no standalone `.cursor/agents/` directory; its developer agents are skill-based roles. Those compatible roles are represented by mirrored skills here.

## Work kanban

Follow `work/README.md` + `work-tracker` skill. After MD moves:

```bash
bash scripts/check-work-kanban.sh
```

## Verification

Ask any agent: **"Re-read CLAUDE.md and confirm current rules"**

Expect: non-negotiables summarized, domain vocabulary, money append-only, tenant isolation, skill awareness.

Also: **"Show work kanban status"** → PLAN / SUMMARY / folder agreement + WIP ≤ 1.

## No Secrets

Never put credentials, tokens, or private URLs in these files.
