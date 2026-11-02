# Change Report Template

Whenever any change is made in this system, it is MANDATORY to create a report file under `work/changes/` in the format below.

File name format: `YYYY-MM-DD--<short-description>.md` (e.g. `2026-11-02--work-tracking-structure.md`)

## Report Format

```markdown
# Change Report: <Short Title>

- **Date**: YYYY-MM-DD
- **By**: <Name / Agent>
- **Change ID**: <optional>

## 1. Summary
What changed, in one line.

## 2. Before
What existed in the system before the change — file path, old value/code/db name, etc.

## 3. After
What exists after the change — file path, new value/code/db name, etc.

## 4. Reason
Why this change was made — requirement, bug fix, feature, setup, etc.

## 5. Files Affected
- `path/to/file1`
- `path/to/file2`

## 6. Verification / Status
- [ ] Tested / Verified?
- [ ] Migrations/Scripts pending?
- [ ] Notes (dependencies, follow-ups)
```

## Rules
1. **Write a report for every change** — small/simple or big.
2. Write the report **immediately after** the change (before declaring done).
3. In `Before` and `After` use **actual values** (file paths, exact names) — never vague.
4. Always include the reason in the `Why`/`Reason` section.
5. Update the tested/verification status.
6. Refresh `work/SUMMARY.md` §4 Changes after writing the report.
