---
name: dev-test-fix-loop
description: Use only when the user explicitly requests an end-to-end implement, test, triage, fix, and regression cycle.
---

# Development and Test Fix Loop

This workflow does not override the repository's code-only defaults. Run tests, builds, linters, browser automation, migrations, or installs only when the user explicitly authorizes them in the current request.

1. Clarify acceptance criteria and inspect current Laravel/Nuxt patterns.
2. Pick up the work item before edits. Implement minimally with `coding-agent`.
3. Add focused regression coverage with `testing-agent` when appropriate.
4. If execution is authorized, run the smallest relevant checks and record exact results.
5. On any failure, use `triage-before-fix` before editing the test or application.
6. For a confirmed defect, create a bug report before the fix and keep the bug/work links connected.
7. Retest the original failure and relevant regression paths only when execution is authorized.
8. Complete only when acceptance criteria and allowed verification are met. Otherwise report what remains and keep the task open.

Never weaken tests, hide failures, install dependencies without approval, or commit/push without explicit user approval.
