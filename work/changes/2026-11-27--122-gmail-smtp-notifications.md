# Change Report: Gmail SMTP todo notifications

- **Date**: 2026-11-27
- **By**: Copilot
- **Change ID**: 122

## 1. Summary
Configured the local todo-completion notifier for Gmail SMTP and the two requested recipients.

## 2. Before
`scripts/.notify.env` did not exist, so `scripts/todo-notify.sh` skipped delivery. The notifier also checked out as CRLF on Windows with `core.autocrlf=true`, which prevented Bash from parsing it.

## 3. After
Created the ignored local `scripts/.notify.env` with `smtp.gmail.com:587`, sender `nexro.services@gmail.com`, and both requested recipients. Normalized app-password display spacing locally without printing or recording the password. Added a path-specific LF rule for the Bash notifier.

## 4. Reason
The user requested SMTP setup for work-completion notifications. The password shared in chat was not saved or reused.

## 5. Files Affected
- `scripts/.notify.env` (gitignored local config)
- `.gitattributes`
- `scripts/todo-notify.sh` (working-tree line endings normalized to LF)
- `work/bugs/BUG-ENV-002-notify-script-crlf-on-windows.md`
- `work/bugs/INDEX.md`
- `work/completed/122-configure-gmail-smtp-notifications.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/MEMORY.md`

## 6. Verification / Status
- [x] `git check-ignore -v scripts/.notify.env` confirms the local config is ignored.
- [x] SMTP authentication succeeded without displaying the credential.
- [x] `bash -n scripts/todo-notify.sh` passed; `git ls-files --eol` confirms LF enforcement.
- [x] SMTP verification email sent to the configured recipients.
- [x] Item 122 moved from In Progress to Completed; PLAN, SUMMARY, and MEMORY now agree.
