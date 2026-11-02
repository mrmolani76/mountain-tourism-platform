# Change Report: AI rules and skills bootstrap

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: setup-ai-rules-v1

## 1. Summary
Added cross-tool AI rules/skills (Cursor, OpenCode, Claude Code, Codex) plus work kanban templates for Mountain Tourism Platform.

## 2. Before
- Repo had only `README.md`, `document/Mountain_MVP_Scope_v1.pdf`, and git metadata.
- No `CLAUDE.md`, agent skills, or `work/` tracking.

## 3. After
- `CLAUDE.md` / `AGENTS.md` / `SETUP_AI_RULES.md` as source of truth + pointers
- `.cursor/rules/*.mdc` (scope, engineering, money, agent roles)
- `.cursor/skills/*` mirrored to `.opencode/skills/` and `.claude/skills/`
- `.opencode/rules.md` + `.codex/README.md`
- `work/` kanban, change-report, and bug templates

## 4. Reason
User asked to define rules and skills like InventorySystem-docker so Cursor, OpenCode, Freebuff, Claude Code, and other agents share the same project rules (Django + Nuxt + Vuetify + MVP domain).

## 5. Files Affected
- `CLAUDE.md`, `AGENTS.md`, `SETUP_AI_RULES.md`
- `.cursor/rules/*`, `.cursor/skills/**`
- `.opencode/rules.md`, `.opencode/skills/**`
- `.claude/skills/**`, `.codex/README.md`
- `work/**`

## 6. Verification / Status
- [x] Tree created and skills mirrored (11 skills × 3 tool folders)
- [ ] Runtime/app tests N/A (no backend/frontend scaffold yet)
- [ ] Notes: scaffold `backend/` + `frontend/` next; keep skill mirrors in sync when editing
