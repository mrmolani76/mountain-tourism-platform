# MEMORY — Mountain Tourism Platform (session handoff)

> Purpose: a new session reads this to continue without losing context.
> Repo: `/var/www/html/mountain-tourism-platform`
> Rules: `work/README.md` (Kanban work tracking) + `.opencode/skills/` (`work-tracker`, `bug-tracker`, `change-report`).

---

## Project state

- **Name**: Mountain Tourism Platform
- **Repo**: https://github.com/mrmolani76/mountain-tourism-platform (private)
- **Stack**: not scaffolded yet — see `document/Mountain_MVP_Scope_v1.pdf`
- **Docs**: `docs/` (project & feature docs)
- **Tracking**: `work/` (plan, bugs, changes, CI reports)
- **MVP scope**: `document/Mountain_MVP_Scope_v1.pdf`

## Current focus

- Initial setup complete (repo + docs/work tracking structure). No application code yet.

## Next steps

- Read the MVP scope PDF and break it into `work/todo/NN-*.md` items in `work/PLAN.md`.
- Scaffold the application, then fill `docs/PROJECT_GUIDE.md`.

## Completed this session

- Repository created and published, then made private.
- Work tracking (Kanban) + bug tracking + change-report structure added under `work/`.
- Docs structure added under `docs/`.

## Rules reminders

- Work item file moves: `work/todo/` → `work/inprogress/` → `work/completed/`; update `work/PLAN.md` on every move.
- WIP limit = 1.
- Every confirmed bug gets a `work/bugs/BUG-<TYPE>-<NNN>-<slug>.md` report + a row in `work/bugs/INDEX.md` **before** the fix.
- Every change gets a `work/changes/YYYY-MM-DD--<slug>.md` report.
- Ask the user before `git commit` / `git push`.
- Never write secrets/tokens/passwords into tracking docs.
