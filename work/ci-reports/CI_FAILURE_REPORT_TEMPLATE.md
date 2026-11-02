# CI Failure Report: <short title>

- **Date**: YYYY-MM-DD
- **Run ID**: <GitHub Actions run id>
- **Run URL**: <https://github.com/.../actions/runs/...>
- **Workflow**: `<workflow file>`
- **Branch**: <branch>
- **Commit**: `<full sha>` (`<short>`)
- **Actor**: <who pushed / triggered>
- **Overall conclusion**: failure | cancelled | timed_out

## 1. Summary
One or two sentences: which jobs failed and the most likely root cause class.

## 2. Failed jobs
| Job | Conclusion | Duration | First failing step |
|-----|------------|----------|--------------------|
| | | | |

## 3. Triage class (pick one)
| Class | Meaning |
|-------|---------|
| **T1** | Infra/env/CI config/lockfile/permissions/network |
| **T2** | Baseline-known failure only (no new regressions) |
| **T3** | App-code regression |
| **T4** | Test-code / fixture / setup wrong |

**Chosen class**: T?

## 4. Evidence
Paste the decisive log lines (redact secrets). Include the command that failed.

```
<log excerpt>
```

## 5. Root cause (best current theory)
Concrete path + mismatch.

## 6. Suggested fix (do NOT apply in this automation)
- Step 1 …
- Step 2 …

## 7. Deploy / CI gate impact
- [ ] CI gate will stay red → deploy skipped by design
- [ ] Manual deploy only if the owner explicitly asks

## 8. Out of scope (this report run)
- No app/code fix applied
- No baseline edits
- No production deploy / migrate / SSH
- No force-push / history rewrite

## 9. Next owner action
What the human should do after `git pull` (e.g. regenerate lockfile, re-run CI).
