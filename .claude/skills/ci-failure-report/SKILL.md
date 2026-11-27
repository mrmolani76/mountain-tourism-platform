---
name: ci-failure-report
description: Create a report-only summary of a failed CI run when requested. Do not fix code, commit, push, or deploy.
---

# CI Failure Report

Use only when the user asks for a CI failure report or a report-only workflow.

1. Inspect the provided run or use `gh` read-only commands when available.
2. Apply `ci-failure-triage` classification and cite the job/step evidence.
3. Redact secrets and include only the decisive log excerpt.
4. Write a report only if requested, using an existing repository report template or a concise Markdown structure.
5. Stop after the report. Do not change application/tests/CI, commit, push, open a PR, or deploy.

Follow `work-tracker` and `change-report` if the report is part of tracked repository work.
