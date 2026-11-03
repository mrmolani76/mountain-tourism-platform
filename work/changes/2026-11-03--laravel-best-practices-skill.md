# Change Report: Add laravel-best-practices skill (from EQMS)

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: skills-laravel-bp

## 1. Summary
Copied EQMS/Complere `laravel-best-practices` skill into this repo, adapted for `backend-laravel/`, and wired into skill indexes + a Cursor rule.

## 2. Before
- No `laravel-best-practices` under `.cursor/skills/`, `.claude/skills/`, or `.opencode/skills/`
- Skills index listed only `django-best-practices` for backend
- No `.cursor/rules/laravel-best-practices.mdc`

## 3. After
- Full skill + 20 rule files under `.cursor/skills/laravel-best-practices/` (mirrored to `.claude/` and `.opencode/`)
- `SKILL.md` adds Mountain Tourism defaults (Modules, tenant DB, money append-only, AuthZ)
- Requestable rule: `.cursor/rules/laravel-best-practices.mdc`
- Indexes updated: `CLAUDE.md`, `AGENTS.md`, `.opencode/rules.md`
- `coding-agent` stack table mentions `backend-laravel/` + this skill

## 4. Reason
User asked to take Laravel best practices skill/rule from EQMS and add it here for Laravel backend work.

## 5. Files Affected
- `.cursor/skills/laravel-best-practices/SKILL.md` (+ `rules/*.md`)
- `.claude/skills/laravel-best-practices/` (mirror)
- `.opencode/skills/laravel-best-practices/` (mirror)
- `.cursor/rules/laravel-best-practices.mdc`
- `.cursor/skills/coding-agent/SKILL.md` (+ mirrors)
- `CLAUDE.md`
- `AGENTS.md`
- `.opencode/rules.md`
- `work/changes/2026-11-03--laravel-best-practices-skill.md`

## 6. Verification / Status
- [x] Skill mirrors in sync (diff check)
- [ ] Runtime agent discovery (needs new chat / skill reload)
- [x] Notes: Source was `/var/www/html/eQMS/complere/.claude/skills/laravel-best-practices/`
