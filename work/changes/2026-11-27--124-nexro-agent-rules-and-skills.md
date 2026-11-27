# Change Report: Nexro Agent Rules and Skills

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 124

## 1. Summary
Adapted compatible Nexro agent workflows to this repository and synchronized the skill set across Cursor, Claude, and OpenCode.

## 2. Before
- `AGENTS.md`, `CLAUDE.md`, and `.opencode/rules.md` listed the original Mountain Tourism skills only.
- The project did not include several compatible CI, browser E2E, PR, testing, auth, and interruption workflows.
- No adaptation notes explained which Nexro-specific workflows were excluded or rewritten.

## 3. After
- Added five Cursor workflow rules and aligned its engineering standards with `.opencode/rules.md`.
- Added 12 skills under `.cursor/skills/`, mirrored byte-for-byte to `.claude/skills/` and `.opencode/skills/`.
- Updated all three skill indexes to list 23 available skills.
- Documented stack-specific adaptations and intentional exclusions in `SETUP_AI_RULES.md`.
- Preserved `CLAUDE.md` as the authority for product scope, tenant isolation, append-only money records, and explicit Git approval.

## 4. Reason
The user requested Nexro's agent rules and skills be applied to this repository. Nexro-specific production, ERP, and auto-ship behavior must not override this platform's architecture, stack, MVP scope, or approval requirements.

## 5. Files Affected
- `AGENTS.md`, `CLAUDE.md`, `SETUP_AI_RULES.md`
- `.cursor/rules/` and `.cursor/skills/`
- `.claude/skills/`
- `.opencode/rules.md` and `.opencode/skills/`
- `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`
- `work/completed/124-align-nexro-agent-rules-and-skills.md`

## 6. Verification / Status
- [x] The same 23 skill directories and SHA-256 file contents were verified across Cursor, Claude, and OpenCode.
- [x] `AGENTS.md`, `CLAUDE.md`, and `.opencode/rules.md` each index all 23 skills.
- [x] All `.cursor/skills/` references in Cursor rules and skills resolve.
- [x] `bash scripts/check-work-kanban.sh` passed after removing CR characters from its input; direct Windows invocation encounters the script's CRLF `pipefail` line.
- [x] `git diff --check` passed for the edited tracked rule/index files.
- [x] Tests/builds not run by design; this change only updates documentation and agent guidance.
- [x] No packages, migrations, or product code changed.
