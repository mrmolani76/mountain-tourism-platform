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
