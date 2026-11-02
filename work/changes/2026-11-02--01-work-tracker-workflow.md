# Change Report: Harden work-tracker workflow

- **Date**: 2026-11-02
- **By**: Agent
- **Change ID**: 01

## 1. Summary
Locked work kanban so folder, frontmatter, PLAN, and SUMMARY stay in sync, with a check script and stronger agent skill/rule.

## 2. Before
- Short `work/README.md` and thin `work-tracker` skill
- No `work-tracker.mdc`, no MEMORY.md, no `check-work-kanban.sh`
- CLAUDE Rule 10 was one line without a full Work Tracking section

## 3. After
- Full flow in `work/README.md` + expanded skill (create/pickup/block/complete/sync)
- Always-apply `.cursor/rules/work-tracker.mdc`
- `work/MEMORY.md`, richer `TEMPLATE.md`
- `scripts/check-work-kanban.sh`
- CLAUDE § Work Tracking; AGENTS + verify/change-report skills aligned
- Work item `01` archived under `work/completed/`

## 4. Reason
User: keep work-track workflow correct.

## 5. Files Affected
- `work/README.md`, `work/TEMPLATE.md`, `work/MEMORY.md`, `work/PLAN.md`, `work/SUMMARY.md`
- `work/completed/01-work-tracker-workflow.md`
- `.cursor/skills/work-tracker/SKILL.md`
- `.cursor/skills/verify-before-done/SKILL.md`
- `.cursor/skills/change-report/SKILL.md`
- `.cursor/rules/work-tracker.mdc`
- `CLAUDE.md`, `AGENTS.md`
- `scripts/check-work-kanban.sh`
- `.opencode/skills/*` and `.claude/skills/*` mirrors (sync)

## 6. Verification / Status
- [x] `bash scripts/check-work-kanban.sh`
- [ ] App tests N/A
- [ ] Notes: agents must pickup → inprogress before edits; WIP=1
