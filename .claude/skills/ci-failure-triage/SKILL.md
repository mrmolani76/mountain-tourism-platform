---
name: ci-failure-triage
description: Activate when a GitHub Actions run fails or the user asks why CI is red. Inspect evidence and classify the failure before recommending edits.
---

# CI Failure Triage

## Workflow

1. Identify the run, commit, failed jobs, failed steps, and first useful error.
2. Classify with evidence:
   - Environment/infrastructure: runner, service, network, permissions, or configuration.
   - Existing known issue: a tracked failure, not a reason to hide it.
   - Application regression: product behavior changed or broke.
   - Test/setup defect: fixture, assertion, or test configuration is wrong.
3. Do not assume a red check is an application defect. Compare changed files, logs, and nearby test setup.
4. If the user has not authorized test execution, keep investigation read-only and report the next command instead.
5. For a confirmed app bug, create/update the bug report before proposing a code fix.

## Guardrails

- Never weaken/delete assertions, suppress a failed job, or manipulate baselines to make CI green.
- Never bypass repository-required checks or run a deployment to get around a red check.
- Do not expose secrets from logs. Redact credentials, tokens, private keys, and personal data.
- Code edits use `coding-agent`; test-only edits use `testing-agent`.
- Follow work tracking, change reports, verification, and explicit commit/push approval.

## Report

State the run and failing job, evidence, classification, impact, whether reproduced, and the safest next action. Do not claim tests passed without an actual run.
