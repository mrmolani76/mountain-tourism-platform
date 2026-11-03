# Change Report: Todo-completion email notification (Gmail SMTP)

- **Date**: 2026-11-03
- **By**: OpenCode agent (user-requested)
- **Change ID**: mail-notify

## 1. Summary
Every completed kanban todo now triggers an email to `anhali736@gmail.com` (Mountain Tourism platform). SMTP via `nexro.services@gmail.com` (Gmail app password), credentials kept machine-local and gitignored. Reference: InventorySystem-docker mail setup (`.env.example`: smtp.gmail.com:587).

## 2. Before
- No `.gitignore` (secrets risk), no mail hook on work-item completion
- User asked: mail on every todo completion to anhali736@gmail.com, Mountain Tourism platform, ref InventorySystem-docker

## 3. After
- `.gitignore` (new): Python/Node caches + `.env`, `.env.*` (keeps `!.env.example`), `scripts/.notify.env`
- `scripts/.notify.env` (new, chmod 600, gitignored, never committed): SMTP_HOST/PORT/USER/PASSWORD, FROM, NOTIFY_TO=anhali736@gmail.com
- `scripts/todo-notify.sh` (new, executable): resolves item (arg or latest in `work/completed/`), extracts frontmatter + Completion Log, sends mail via smtplib+STARTTLS; **never fails the workflow** (exits 0 with a warning on any error)
- Work-tracker skill (all 3 mirrors: `.opencode`, `.cursor`, `.claude`) — completion flow now includes: `bash scripts/todo-notify.sh NN`
- `work/README.md` — same step documented
- Test: `bash scripts/todo-notify.sh 28` → `[  OK ] Email sent to anhali736@gmail.com — item 28`

## 4. Reason
User request (2026-11-03): "mail setup karo … har todo list complete hone pr mail anhali736@gmail.com is gmail pr mil aayegi … ref inventorysystem docker".

## 5. Files Affected
- `.gitignore`, `scripts/.notify.env` (gitignored), `scripts/todo-notify.sh`
- `.opencode/skills/work-tracker/SKILL.md`, `.cursor/skills/work-tracker/SKILL.md`, `.claude/skills/work-tracker/SKILL.md`
- `work/README.md`

## 6. Verification / Status
- [x] SMTP auth + STARTTLS send OK (item 28 test mail delivered)
- [x] `scripts/.notify.env` not tracked by git (gitignored; verified via `git status --ignored`)
- [x] Kanban check OK
- Notes: password is a Gmail **app password** (not the account password); belongs to nexro.services@gmail.com session scope only. Rotate if it leaks. Notifications include internal item titles — sent only to the user's own mailbox.

## 7. Usage
```bash
bash scripts/todo-notify.sh        # latest completed item
bash scripts/todo-notify.sh 31     # specific item
# runs automatically on every work-tracker completion
```
