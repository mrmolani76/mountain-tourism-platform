---
title: Configure Gmail SMTP todo notifications
id: 122
priority: medium
status: completed
milestone: none
module: infra
area: devops
effort: S
dependencies: none
bug: BUG-ENV-002
created: 2026-11-27
updated: 2026-11-27
---

# Configure Gmail SMTP todo notifications

## What to do (What)
Configure the local kanban completion notifier to send through the Nexro Services Gmail account to the requested recipients.

## Current / Before
`scripts/todo-notify.sh` reads `scripts/.notify.env`, but the ignored local config file is missing.

## Expected / After
The local config contains Gmail SMTP host, STARTTLS port, sender, and notification recipients. A newly generated app password is entered locally and is never committed or included in chat.

## Why
The user requested email notifications for work-item completion.

## Acceptance Criteria (complete only when all ticked)
- [x] Configure `smtp.gmail.com:587` with STARTTLS and sender `nexro.services@gmail.com`.
- [x] Send an SMTP verification email to `anhali736@gmail.com` and `rubyasop@gmail.com`.
- [x] Store the rotated Gmail app password only in the ignored local config.
- [x] Verify the config is gitignored and verify SMTP authentication after the password is added.
- [x] Keep the Bash notifier executable from the Windows checkout.
- [x] `work/changes/2026-11-27--122-gmail-smtp-notifications.md` written.
- [x] PLAN.md + SUMMARY.md updated.

## Files Likely Affected
- `.gitattributes`
- `scripts/.notify.env` (ignored local config)
- `scripts/todo-notify.sh`
- `work/bugs/BUG-ENV-002-notify-script-crlf-on-windows.md`
- `work/PLAN.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-27--122-gmail-smtp-notifications.md`

## Notes / Follow-ups
- The app password is stored only in the ignored local config and is not included in repository files or reports.
- Added a path-specific LF rule for the notifier. `bash -n` passes, SMTP authentication succeeds, and an SMTP verification email was sent. See [BUG-ENV-002](../bugs/BUG-ENV-002-notify-script-crlf-on-windows.md).

---

## Return to Pending Log (when work stops midway)

---

## Completion Log (when work is complete)

- **Completed by**: Copilot
- **Date**: 2026-11-27
- **What was done**: Configured and verified the local Gmail SMTP notifier, corrected Windows line-ending handling, and synchronized kanban records.
- **Files changed**: `.gitattributes`, `scripts/todo-notify.sh`, `work/bugs/BUG-ENV-002-notify-script-crlf-on-windows.md`, `work/bugs/INDEX.md`, `work/PLAN.md`, `work/SUMMARY.md`, `work/MEMORY.md`, and this work item. The ignored local SMTP config remains untracked.
- **Verification**: SMTP authentication and email delivery passed; `bash -n scripts/todo-notify.sh` passed; `git check-ignore -v scripts/.notify.env` confirmed the local config is ignored.
- **Change report**: [2026-11-27--122-gmail-smtp-notifications.md](../changes/2026-11-27--122-gmail-smtp-notifications.md)
- **PLAN / SUMMARY**: Moved item 122 from In Progress to Completed in both files.
