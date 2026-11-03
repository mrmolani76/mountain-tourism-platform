# Change Report: Remove Python/Django agent guidance

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: drop-django-skills

## 1. Summary
Removed `django-best-practices` and retargeted live agent rules/skills/docs from Django/Python to Laravel (`backend-laravel/` only).

## 2. Before
- `django-best-practices` skill in `.cursor/`, `.claude/`, `.opencode/`
- `CLAUDE.md` / `AGENTS.md` / coding + testing + security skills still described Django + `backend/`
- Money rule globs pointed at `backend/**/*.py`

## 3. After
- Django skill directories deleted
- Stack + architecture sections say Laravel + Modules under `backend-laravel/`
- Skills index lists `laravel-best-practices` only for backend
- Coding / testing / security agents and related `.mdc` rules updated
- Money append-only globs: `backend-laravel/**/*.{php}`

## 4. Reason
User: Python is not in the repo; remove Python-related agent guidance. Django `backend/` already removed earlier.

## 5. Files Affected
- Deleted: `.cursor/skills/django-best-practices/`, mirrors under `.claude/` and `.opencode/`
- `CLAUDE.md`, `AGENTS.md`, `.opencode/rules.md`
- `.cursor/skills/coding-agent/SKILL.md`, `testing-agent/SKILL.md`, `security-code-review/SKILL.md` (+ mirrors)
- `.cursor/rules/coding-agent.mdc`, `testing-agent.mdc`, `scope.mdc`, `money-append-only.mdc`
- `README.md` (stack line)
- `work/changes/2026-11-03--remove-python-django-agent-guidance.md`

## 6. Verification / Status
- [x] No `django-best-practices` under skills dirs
- [x] Active agent paths scanned for leftover Django/pytest/`backend/**` guidance
- [x] Notes: Historical `work/` / `docs/` decision logs kept as history (not rewritten)
