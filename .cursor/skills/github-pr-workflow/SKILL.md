---
name: github-pr-workflow
description: Use when the user explicitly requests a GitHub issue, branch, commit, push, or pull-request workflow.
---

# GitHub PR Workflow

This skill does not create a default branch policy for the repository. Follow the user's requested workflow and current repository branch conventions.

- Confirm the target repository and branch before any GitHub write.
- Do not create issues, branches, commits, pushes, or PRs unless explicitly requested.
- Explicit approval to start work is not approval to commit, push, or open a PR.
- Never force-push, merge, or bypass CI.
- Stage only task files. Preserve unrelated worktree changes.
- Include actual verification results and risk notes in a PR description.
- Keep work kanban and change reports aligned with the change.

Ask the user before any requested action that lacks clear same-exchange approval.
