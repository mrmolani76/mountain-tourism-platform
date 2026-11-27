---
id: BUG-ENV-002
title: Todo notification script fails with Windows CRLF checkout
status: VERIFIED
type: ENV
severity: medium
created: 2026-11-27
---

# BUG-ENV-002: Todo notification script fails with Windows CRLF checkout

## 1. Summary
The todo-completion notifier fails to start when Git checks out its Bash script with CRLF line endings on Windows.

## 2. Environment
Local Windows checkout used through WSL Bash; `core.autocrlf=true`.

## 3. Steps to Reproduce
1. In WSL, run `bash scripts/todo-notify.sh 122`.

## 4. Expected
The notifier starts and sends the configured completion email.

## 5. Actual
The script exits before running with `set: pipefail\r: invalid option name`.

## 6. Evidence
Before the fix, `git ls-files --eol -- scripts/todo-notify.sh` reported `i/lf w/crlf`; running the notifier returned exit code 2 at line 9. After the fix, the file reports `i/lf w/lf attr/text eol=lf`, `bash -n scripts/todo-notify.sh` passes, and SMTP verification email delivery succeeded.

## 7. Root Cause
The repository has no root `.gitattributes` rule to preserve LF for this Bash script, while Git's `core.autocrlf=true` converts the working-tree copy to CRLF.

## 8. Workaround
The repository now preserves LF for this script.

## 9. Suggested Fix
Verified: `.gitattributes` now preserves LF for `scripts/todo-notify.sh`.

## 10. Impact
Work-item completion notifications do not run from affected Windows/WSL checkouts.

## 11. Related
- Work item: `work/inprogress/122-configure-gmail-smtp-notifications.md`
- Change report: `work/changes/2026-11-27--122-gmail-smtp-notifications.md`
- Other bugs: none
