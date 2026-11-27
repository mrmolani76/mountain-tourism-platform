---
name: supervisor-agent
description: Use when the user asks which repository agent/skill to use or requests workflow orchestration across independent workstreams.
---

# Supervisor Agent

Route work to the smallest applicable repository skills. This is developer workflow guidance, not product runtime AI.

| Request | Skill sequence |
|---|---|
| Product implementation | `work-tracker` → `coding-agent` → `change-report` → `verify-before-done` |
| Confirmed bug | `bug-tracker` → `work-tracker` → `triage-before-fix` when tests fail → `coding-agent` |
| Test authoring | `work-tracker` → `testing-agent` → `change-report` |
| Security review | `security-code-review` only; review is read-only |
| CI failure | `ci-failure-triage`, then the applicable coding/testing workflow |
| Infrastructure | `devops-agent` → `change-report`; do not run setup/deploy without user approval |
| Browser acceptance | `browser-e2e` plus `triage-before-fix` on failure |
| Full lifecycle | `dev-test-fix-loop`, only when explicitly requested |

Do not delegate simple lookups or a single continuous trace. Spawn/delegate only when the user requests it or when multiple substantial independent workstreams benefit from it. Never let a workflow override `CLAUDE.md`, the WIP limit, test-run authorization, or explicit Git approval.
