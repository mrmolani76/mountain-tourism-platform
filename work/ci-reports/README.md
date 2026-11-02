# CI failure reports

When GitHub Actions fails, the CI Failure Cloud Report automation (or a human/agent) should:

1. Inspect the failed run (jobs + decisive logs).
2. Classify T1 / T2 / T3 / T4 (see the template).
3. Write one report under this folder.
4. Commit **only** that report file (and optionally a one-line index touch if needed).
5. Stop. A human pulls and decides the fix.

## File name

```
work/ci-reports/YYYY-MM-DD--run-<runId>--<short-slug>.md
```

Example: `2026-11-02--run-1234567890--landing-frozen-lockfile.md`

## Template

Copy structure from `CI_FAILURE_REPORT_TEMPLATE.md`.

## Hard rules for the agent

- Report + commit only. No code fixes. No baseline edits. No deploy.
- Never weaken the CI gate or path filters to go green.
- Redact secrets from logs.
