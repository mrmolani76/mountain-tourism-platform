---
title: Align Nexro agent rules and skills
id: 124
priority: high
status: completed
milestone: none
module: docs
area: docs
effort: L
dependencies: none
bug: none
created: 2026-11-27
updated: 2026-11-27
---

# Align Nexro agent rules and skills

## What to do (What)
Audit the local Nexro repository's agent instructions, Cursor rules, and skills. Apply the relevant workflows to this repository's AI instructions and synchronize the Cursor, Claude, and OpenCode mirrors.

## Current / Before
This repository already has Mountain Tourism-specific `CLAUDE.md`, `.cursor/rules/`, and skills mirrored to `.claude/skills/` and `.opencode/skills/`. It does not yet include all compatible Nexro engineering workflows and agent skills.

## Expected / After
Compatible Nexro agent workflows and skills are available and discoverable in all supported repository AI clients, rewritten for the Laravel + Nuxt mountain-tourism product and subordinated to this repository's domain, scope, security, and git-approval rules.

## Why
The user requested Nexro's agents, rules, and skills to be applied to this repository.

## Acceptance Criteria (complete only when all ticked)
- [x] Audit Nexro source rules and skills against this repository's stack, domain, and non-negotiables.
- [x] Add or align all compatible agent rules and skill workflows across Cursor, Claude, and OpenCode.
- [x] Do not import Nexro product-specific workflows or instructions that conflict with this repository's rules.
- [x] Update `AGENTS.md`, `CLAUDE.md` references, and `SETUP_AI_RULES.md` so agents discover the complete set.
- [x] Check that mirrors are consistent and links point to valid repository files.
- [x] `work/changes/2026-11-27--124-nexro-agent-rules-and-skills.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `AGENTS.md`
- `CLAUDE.md`
- `SETUP_AI_RULES.md`
- `.cursor/rules/`
- `.cursor/skills/`
- `.claude/skills/`
- `.opencode/rules.md`
- `.opencode/skills/`
- `work/PLAN.md`
- `work/SUMMARY.md`

## Notes / Follow-ups
- Nexro-specific ERP workflows must be adapted, not copied verbatim.
- This repository's explicit user approval rule for commits and pushes takes precedence over any source workflow that auto-commits or auto-pushes.

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Added five Cursor workflow rules, aligned the engineering standards twin, and added 12 Nexro-inspired skills in Cursor, Claude, and OpenCode. Updated all skill indexes and documented stack-specific adaptations and intentional exclusions.
- **Files changed**: `AGENTS.md`, `CLAUDE.md`, `SETUP_AI_RULES.md`, `.cursor/rules/`, `.cursor/skills/`, `.claude/skills/`, `.opencode/rules.md`, `.opencode/skills/`, `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`, this work item, and the change report.
- **Verification**: All 23 skill names and SHA-256 file contents match across the three clients; all three indexes list 23/23 skills; skill references resolve. Kanban checker passed after normalizing CRLF input; direct Windows invocation reports `pipefail\r`. No tests/builds run because this is documentation and agent guidance only.
- **Change report**: `work/changes/2026-11-27--124-nexro-agent-rules-and-skills.md`
- **PLAN / SUMMARY**: Item 124 moved from In Progress to Completed and dashboard/handoff updated.
