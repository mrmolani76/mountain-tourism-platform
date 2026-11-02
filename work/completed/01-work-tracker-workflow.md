---
title: Harden work-tracker kanban workflow
id: 01
priority: high
status: completed
milestone: none
module: ai-rules
area: docs
effort: S
dependencies: none
bug: none
created: 2026-11-02
updated: 2026-11-02
---

# Harden work-tracker kanban workflow

## What to do (What)
Make work tracking match Inventory-style flow: folder=status, PLAN three tables, SUMMARY sync, pickup before edits, complete + change report, validation script.

## Current / Before
Thin `work-tracker` skill and short `work/README.md`; easy for agents to skip PLAN/SUMMARY moves.

## Expected / After
Full README + skill checklists, Cursor rule, CLAUDE § Work Tracking, `scripts/check-work-kanban.sh`, MEMORY.md, tighter verify/change-report links.

## Why
User asked to keep the work-track workflow correct before project scaffold.

## Acceptance Criteria (complete only when all ticked)
- [x] `work/README.md` full flow
- [x] `work-tracker` skill with create/pickup/block/complete/sync
- [x] `.cursor/rules/work-tracker.mdc`
- [x] CLAUDE + AGENTS updated
- [x] `scripts/check-work-kanban.sh` passes
- [x] Change report written
- [x] PLAN.md + SUMMARY.md updated

## Files Likely Affected
- `work/**`
- `.cursor/skills/work-tracker/`
- `.cursor/rules/work-tracker.mdc`
- `CLAUDE.md`, `AGENTS.md`
- `scripts/check-work-kanban.sh`

## Notes / Follow-ups
- Mirror skills to `.opencode/` and `.claude/` after edits.

---

## Completion Log (when work is complete)

- **Completed by**: Agent
- **Date**: 2026-11-02
- **What was done**: Hardened kanban workflow docs, skill, rule, check script, CLAUDE section; archived this item as 01.
- **Files changed**: work/README.md, TEMPLATE.md, MEMORY.md, PLAN.md, SUMMARY.md, .cursor/skills/work-tracker, verify-before-done, change-report, .cursor/rules/work-tracker.mdc, CLAUDE.md, AGENTS.md, scripts/check-work-kanban.sh, mirrors
- **Verification**: `bash scripts/check-work-kanban.sh`
- **Change report**: `work/changes/2026-11-02--01-work-tracker-workflow.md`
- **PLAN / SUMMARY**: updated yes
