# Change Report: Code-first, no setup by default

- **Date**: 2026-11-03
- **By**: Agent
- **Change ID**: rules-no-setup

## 1. Summary
Standing rule: agents write product/API code; project setup is off unless the user asks.

## 2. Before
- `CLAUDE.md` Focus: backend/API first; frontend deferred. Setup (Docker, VPS, CI, seed) was allowed as normal work.
- `AGENTS.md` Key Project Facts: no explicit ban on setup-first work.
- `work/SUMMARY.md` Focus: backend/API only until frontend opens.

## 3. After
- `CLAUDE.md` Header Focus + Stack delivery table: no Docker/Compose/VPS/CI/env/seed/infra unless user asks; prefer unfinished module features.
- `AGENTS.md`: Key fact "No setup by default".
- `work/SUMMARY.md` Focus line matches.

## 4. Reason
User: write code, not project setup; "setup krna hi nhi h".

## 5. Files Affected
- `CLAUDE.md`
- `AGENTS.md`
- `work/SUMMARY.md`
- `work/changes/2026-11-03--code-first-no-setup-rule.md`

## 6. Verification / Status
- [x] Rules text updated (source of truth = CLAUDE.md)
- [ ] Migrations/Scripts pending? N/A
- [ ] Notes: setup still allowed only when user explicitly requests it in that message
