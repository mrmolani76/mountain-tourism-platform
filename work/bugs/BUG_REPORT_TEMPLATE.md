# BUG Report Template

**Any confirmed bug (testing/deploy/runtime — app, config, env, infra) requires a mandatory report** under `work/bugs/`. File name format: `BUG-<TYPE>-<NNN>-<short-description>.md` (e.g. `BUG-APP-001-booking-payment-status-missing.md`, `BUG-ENV-001-deploy-env-file-missing.md`).

## Bug Type Codes
| Code | Meaning |
|------|---------|
| `APP` | Application code bug (feature/business logic/UI) |
| `ENV` | Environment/infra/docker/deploy/config bug |
| `API` | API contract/DTO/validation response bug |
| `DATA` | Data integrity/migration/seed bug |
| `AUTH` | Authentication/authorization/session bug |
| `TEST` | Test/spec bug (write as TEST, but if it masks an app bug → APP) |
| `FLAKY` | Flaky test (noise; do not create app BUG without investigation) |

**Sequence**: `BUG-<TYPE>-001`, `002`, ... — per type counter.

## Report Format

```markdown
# BUG-<TYPE>-<NNN> — <short title>

- **Bug ID**: BUG-<TYPE>-<NNN>
- **Created**: YYYY-MM-DD
- **Severity**: Critical / High / Medium / Low
- **Priority**: Immediate / High / Medium / Low
- **Category**: <TYPE code> (e.g. ENVIRONMENT_BUG)
- **Status**: BUG_CREATED
  (flow: BUG_CREATED → BUG → TODO → IN_PROGRESS → BUG_FIX_IN_PROGRESS → RETESTING → VERIFIED → COMPLETED)

## 1. Summary
One line — severity and what should have happened.

## 2. Environment
- Machine / deploy commit / stack version / browser / URL / mobile.

## 3. Steps to Reproduce
1. ...

## 4. Expected
Correct behavior.

## 5. Actual
What happened (error message, status code, screenshot/video reference if any).

## 6. Evidence / Logs
- Error stack, server log lines, network panel, console error.
- Never paste secrets/credentials/passwords.

## 7. Root Cause
Proven cause (code line / config / ordering / env). No guessing — investigate with evidence.

## 8. Workaround
Temporary manual fix (if any).

## 9. Suggested Fix
Concrete change (file/line + approach). Optional second/third options.

## 10. Impact
Users affected / features broken / data risk.

## 11. Related
- Task ID, Test Case ID, Deploy commit, change report link.
```

## Rules
1. **Create the report as soon as the bug is confirmed** — before the fix (BUG_CREATED). Update status after the fix.
2. Report file lives under `work/bugs/`; always include it when committing (with user approval).
3. **Do not duplicate the same bug** — search existing `work/bugs/*` first.
4. Bug found during failed deploy / smoke test / regression → report + reference the FAIL entry in deploy history.
5. Test report ≠ bug report — classify with triage before fixing.
6. Never paste secrets — redact passwords/tokens in logs.
