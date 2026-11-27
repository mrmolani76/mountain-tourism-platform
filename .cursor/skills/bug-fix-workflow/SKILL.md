---
name: bug-fix-workflow
description: Use for an explicitly requested bug fix or bug lifecycle. Confirm and track the bug, fix narrowly, and verify only as authorized.
---

# Bug Fix Workflow

1. Check for an existing bug report. Create one before fixing a confirmed bug.
2. Work only on the user-named bug or explicitly approved work item. Do not pick the next backlog item on your own.
3. Use `triage-before-fix` for failed or flaky tests before editing.
4. Follow `coding-agent` for application code and `testing-agent` for test-only changes.
5. Preserve money append-only, tenant isolation, server authorization, and migration immutability.
6. Run checks only when the user authorizes execution. Report precise evidence and remaining gaps.
7. Update bug status, work tracking, and the change report together.
8. Ask before commit, push, PR creation, or deployment. This skill never grants auto-ship permission.
